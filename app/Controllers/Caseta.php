<?php

namespace App\Controllers;

use App\Libraries\Notify;
use App\Models\AccesoEventoModel;
use App\Models\AccesoModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Gate (caseta) operations: scan a QR and register entry/exit on an acceso,
 * capturing vehicle/pax/ID data at check-in. Gated by caseta.operate and
 * scoped to the operator's active condominio.
 */
class Caseta extends BaseController
{
    private AccesoModel $model;

    public function __construct()
    {
        $this->model = new AccesoModel();
    }

    public function escaner(): string
    {
        return view('caseta/escaner', ['title' => 'Escanear QR']);
    }

    /**
     * Direct gate registration for arrivals no resident pre-registered:
     * paquetería (stays at the gate until picked up), delivery and proveedor
     * (walk in and out). Notifies the casa's resident.
     */
    public function registro(): string
    {
        $cid   = (int) $this->activeCondominioId();
        $casas = (new \App\Models\CasaModel())->where('condominio_id', $cid)->orderBy('identificador', 'ASC')->findAll();

        $condo    = service('tenant')->active();
        $horarios = [];
        foreach (\App\Libraries\Horario::TIPOS as $t) {
            $horarios[$t] = \App\Libraries\Horario::resumen(\App\Libraries\Horario::forTipo($condo, $t));
        }

        return view('caseta/registro', [
            'title'         => 'Registrar paquetería / entrega',
            'casas'         => $casas,
            'destinatarios' => \App\Libraries\CasaResidents::mapForCondominio($cid),
            'horarios'      => $horarios,
        ]);
    }

    public function registrar(): RedirectResponse
    {
        $cid  = (int) $this->activeCondominioId();
        $tipo = $this->request->getPost('tipo');
        if (! in_array($tipo, ['paqueteria', 'delivery', 'proveedor'], true)) {
            return redirect()->back()->withInput()->with('error', 'Tipo de registro inválido.');
        }

        $casaId = (int) $this->request->getPost('casa_id');
        $casa   = (new \App\Models\CasaModel())->where('condominio_id', $cid)->find($casaId);
        if ($casa === null) {
            return redirect()->back()->withInput()->with('error', 'Selecciona una casa de este condominio.');
        }

        if (! $this->validate([
            'nombre_visitante' => 'required|max_length[150]',
            'foto'             => 'permit_empty|is_image[foto]|max_size[foto,5120]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Recipient: caseta's pick, validated against the casa's residents; else the default.
        $destId   = (int) $this->request->getPost('destinatario_persona_id');
        $residents = \App\Libraries\CasaResidents::forCasa($casaId);
        $validIds  = array_map(static fn ($r) => (int) $r['id'], $residents);
        if ($destId <= 0 || ! in_array($destId, $validIds, true)) {
            $destId = \App\Libraries\CasaResidents::defaultRecipient($casaId) ?? 0;
        }

        $esPaqueteria = $tipo === 'paqueteria';
        $now          = date('Y-m-d H:i:s');

        $data = [
            'condominio_id'          => $cid,
            'casa_id'                => $casaId,
            'tipo'                   => $tipo,
            'solicitante_persona_id' => $destId ?: null,
            'creado_por_user_id'     => auth()->id(),
            'nombre_visitante'       => $this->request->getPost('nombre_visitante'),
            'empresa'                => $this->request->getPost('empresa') ?: null,
            'num_personas'           => 1,
            'notas'                  => $this->request->getPost('notas') ?: null,
            'estado'                 => $esPaqueteria ? 'en_caseta' : 'ingresado',
            'caseta_user_id'         => auth()->id(),
        ];
        if (! $esPaqueteria) {
            $data['check_in_at'] = $now;
        }
        if (($path = $this->storeUpload('foto')) !== null) {
            $data['foto_path'] = $path;
        }

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }
        $id = $this->model->getInsertID();

        (new AccesoEventoModel())->log(
            $id,
            $data['estado'],
            null,
            auth()->id(),
            $esPaqueteria ? 'Paquetería recibida en caseta' : ucfirst($tipo) . ' ingresó (registro directo)'
        );

        $acceso = $this->model->find($id);
        if ($esPaqueteria) {
            Notify::acceso($acceso, 'Tienes un paquete en caseta',
                trim(($data['empresa'] ? $data['empresa'] . ' — ' : '') . $data['nombre_visitante']) . '. Recógelo en caseta.',
                'portal/paquetes');
        } else {
            Notify::acceso($acceso, 'Llegó tu ' . AccesoModel::TIPOS[$tipo],
                $data['nombre_visitante'] . ' ingresó al condominio.', 'portal/paquetes');
        }

        return redirect()->to('accesos/' . $id)->with('success',
            $esPaqueteria ? 'Paquetería registrada. Se notificó al residente. 📦' : 'Ingreso registrado. Se notificó al residente. ✅');
    }

    /** Form to hand a package over to the resident (captures a proof photo). */
    public function entregarForm(int $id): string|RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if ($acceso['tipo'] !== 'paqueteria' || $acceso['estado'] !== 'en_caseta') {
            return redirect()->to('accesos/' . $id)->with('error', 'Solo se entrega paquetería que está en caseta.');
        }

        return view('caseta/entregar', ['title' => 'Entregar paquete', 'acceso' => $acceso]);
    }

    /** Paquetería handed over to the resident. */
    public function entregar(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if ($acceso['tipo'] !== 'paqueteria' || $acceso['estado'] !== 'en_caseta') {
            return redirect()->to('accesos/' . $id)->with('error', 'Solo se entrega paquetería que está en caseta.');
        }

        if (! $this->validate(['foto' => 'permit_empty|is_image[foto]|max_size[foto,5120]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = ['estado' => 'entregado', 'check_out_at' => date('Y-m-d H:i:s')];
        if (($path = $this->storeUpload('foto')) !== null) {
            $data['foto_entrega_path'] = $path;
        }
        $this->model->update($id, $data);
        (new AccesoEventoModel())->log($id, 'entregado', 'en_caseta', auth()->id(), 'Paquete entregado al residente');
        Notify::acceso($acceso, 'Paquete entregado',
            trim(($acceso['empresa'] ? $acceso['empresa'] . ' — ' : '') . $acceso['nombre_visitante']) . ' fue entregado.',
            'portal/paquetes');

        return redirect()->to('accesos/' . $id)->with('success', 'Paquete marcado como entregado. 📬');
    }

    public function checkinForm(int $id): string|RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if (! in_array($acceso['estado'], ['programado', 'vencido'], true)) {
            return redirect()->to('accesos/' . $id)->with('error', 'Este acceso no admite registro de entrada.');
        }

        $parking = new \App\Libraries\Parking();

        // Schedule alert for delivery/proveedor arriving outside the allowed window.
        $horario = null;
        if (in_array($acceso['tipo'], \App\Libraries\Horario::TIPOS, true)) {
            $horario = \App\Libraries\Horario::check(service('tenant')->active(), $acceso['tipo']);
        }

        return view('caseta/checkin', [
            'title'         => 'Registrar entrada',
            'acceso'        => $acceso,
            'solicitante'   => $acceso['solicitante_persona_id'] ? (new PersonaModel())->find($acceso['solicitante_persona_id']) : null,
            'cajonesLibres' => $parking->availableVisitorSpots((int) $this->activeCondominioId()),
            'horario'       => $horario,
        ]);
    }

    public function checkin(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if (! in_array($acceso['estado'], ['programado', 'vencido'], true)) {
            return redirect()->to('accesos/' . $id)->with('error', 'Este acceso no admite registro de entrada.');
        }

        if (! $this->validate(['id_foto' => 'permit_empty|is_image[id_foto]|max_size[id_foto,5120]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $vehiculo = $this->request->getPost('ingreso_vehiculo') ? 1 : 0;
        $sinId    = $this->request->getPost('sin_id') ? 1 : 0;
        $pax      = (int) ($this->request->getPost('pax_ingresaron') ?: $acceso['num_personas']);

        $data = [
            'estado'           => 'ingresado',
            'check_in_at'      => date('Y-m-d H:i:s'),
            'caseta_user_id'   => auth()->id(),
            'pax_ingresaron'   => max(1, $pax),
            'ingreso_vehiculo' => $vehiculo,
            'folio_corbatin'   => $vehiculo ? ($this->request->getPost('folio_corbatin') ?: null) : null,
            'sin_id'           => $sinId,
            'id_nota'          => $sinId ? ($this->request->getPost('id_nota') ?: null) : null,
        ];
        if ($vehiculo && $this->request->getPost('placas')) {
            $data['placas'] = $this->request->getPost('placas');
        }
        if (($path = $this->storeIdFoto()) !== null) {
            $data['id_foto_path'] = $path;
        }

        // Parking for vehicles. A free visitor spot is assigned if picked; else,
        // if the resident pre-authorized their own spot (autoriza_cajon_propio) and
        // no visitor spot is free, we use the resident's spot without asking again.
        if ($vehiculo) {
            $parking = new \App\Libraries\Parking();
            $cid     = (int) $this->activeCondominioId();
            $cajonId = (int) $this->request->getPost('cajon_id');
            if ($cajonId && $parking->isFreeVisitorSpot($cid, $cajonId)) {
                $data['cajon_id']           = $cajonId;
                $data['autorizacion_cajon'] = null;
            } elseif ($acceso['autorizacion_cajon'] !== 'autorizado'
                && ! empty($acceso['autoriza_cajon_propio'])
                && $parking->availableVisitorSpots($cid) === []) {
                $data['cajon_id']           = $this->residentCajonId((int) $acceso['casa_id']);
                $data['autorizacion_cajon'] = 'autorizado';
            }
        }

        $this->model->update($id, $data);

        $nota = 'Entrada. Pax: ' . $data['pax_ingresaron']
            . ($vehiculo ? ', vehículo' . ($data['folio_corbatin'] ? ' folio ' . $data['folio_corbatin'] : '') : '')
            . ($sinId ? ', sin ID' : '')
            . ($acceso['autorizacion_cajon'] ? ', cajón residente ' . $acceso['autorizacion_cajon'] : '');
        (new AccesoEventoModel())->log($id, 'ingresado', $acceso['estado'], auth()->id(), $nota);

        Notify::acceso(
            $acceso,
            'Tu visita llegó',
            $acceso['nombre_visitante'] . ' ingresó al condominio' . ($vehiculo ? ' en vehículo' : '') . '.',
            'portal/visitas/' . $id
        );

        return redirect()->to('accesos/' . $id)->with('success', 'Entrada registrada. ✅');
    }

    /** Pre-check-in: request the resident's authorization to use their parking spot. */
    public function solicitarCajon(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }

        $this->model->update($id, ['autorizacion_cajon' => 'pendiente']);
        Notify::acceso(
            $acceso,
            'Autoriza el uso de tu cajón',
            'No hay cajones de visita disponibles para ' . $acceso['nombre_visitante']
            . '. Autoriza o rechaza el uso de tu cajón.',
            'portal/autorizaciones'
        );

        return redirect()->to('caseta/accesos/' . $id . '/checkin')
            ->with('success', 'Solicitud enviada al residente. Espera su respuesta o fuérzala si ya lo confirmó por teléfono.');
    }

    /** Pre-check-in: caseta forces the authorization (already confirmed by phone). */
    public function forzarCajon(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }

        $this->model->update($id, ['autorizacion_cajon' => 'autorizado', 'cajon_id' => $this->residentCajonId((int) $acceso['casa_id'])]);
        Notify::acceso(
            $acceso,
            'Se usó tu cajón',
            'Se autorizó por teléfono el uso de tu cajón para ' . $acceso['nombre_visitante'] . '.',
            'portal/visitas/' . $id
        );

        return redirect()->to('caseta/accesos/' . $id . '/checkin')
            ->with('success', 'Autorización forzada (por teléfono). Ahora registra la entrada.');
    }

    public function checkout(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if ($acceso['estado'] !== 'ingresado') {
            return redirect()->back()->with('error', 'Solo se registra salida de un acceso que ya ingresó.');
        }

        $this->model->update($id, ['estado' => 'finalizado', 'check_out_at' => date('Y-m-d H:i:s')]);
        (new AccesoEventoModel())->log($id, 'finalizado', 'ingresado', auth()->id(), 'Salida registrada en caseta');

        Notify::acceso($acceso, 'Tu visita salió', $acceso['nombre_visitante'] . ' salió del condominio.', 'portal/visitas/' . $id);

        return redirect()->to('accesos/' . $id)->with('success', 'Salida registrada. 👋');
    }

    private function storeIdFoto(): ?string
    {
        return $this->storeUpload('id_foto');
    }

    /** First active parking spot of a casa (the resident's own cajon), or null. */
    private function residentCajonId(int $casaId): ?int
    {
        $cajon = (new \App\Models\CajonModel())
            ->where('condominio_id', $this->activeCondominioId())
            ->where('casa_id', $casaId)
            ->where('activo', 1)
            ->first();

        return $cajon['id'] ?? null;
    }

    /** Move an uploaded image to public/uploads/accesos and return its relative path. */
    private function storeUpload(string $field): ?string
    {
        $file = $this->request->getFile($field);
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }
        $name = $file->getRandomName();
        $file->move(FCPATH . 'uploads/accesos', $name);

        return 'uploads/accesos/' . $name;
    }

    /** Acceso restricted to the operator's active condominio. */
    private function scoped(int $id): ?array
    {
        return $this->model->where('condominio_id', $this->activeCondominioId())->find($id);
    }
}
