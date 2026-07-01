<?php

namespace App\Controllers;

use App\Libraries\Horario;
use App\Libraries\Notify;
use App\Libraries\Tz;
use App\Models\AccesoEventoModel;
use App\Models\AccesoModel;
use App\Models\CasaPropietarioModel;
use App\Models\OcupanteModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Resident-facing visit passes (tipo=visita). The resident creates a visit for
 * one of their own casas and gets a QR; caseta validation/check-in lands in F2.2.
 */
class Visitas extends BaseController
{
    private AccesoModel $model;
    private PersonaModel $personas;

    public function __construct()
    {
        $this->model    = new AccesoModel();
        $this->personas = new PersonaModel();
    }

    public function index(): string|RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Tu usuario no está vinculado a una persona en este condominio.');
        }

        return view('visitas/index', [
            'title'   => 'Mis visitas',
            'visitas' => $this->model->forSolicitante((int) $persona['id'], ['visita']),
        ]);
    }

    /** Form to announce an expected delivery or proveedor to caseta. */
    public function avisar(): string|RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Tu usuario no está vinculado a una persona en este condominio.');
        }
        $casas = $this->myCasas();
        if ($casas === []) {
            return redirect()->to('portal')->with('error', 'No tienes casas asignadas.');
        }

        $condo    = service('tenant')->active();
        $horarios = [];
        foreach (Horario::TIPOS as $t) {
            $horarios[$t] = Horario::forTipo($condo, $t);
        }

        return view('deliveries/form', [
            'title'    => 'Avisar delivery o proveedor',
            'casas'    => $casas,
            'horarios' => $horarios,
        ]);
    }

    public function crearAviso(): RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Acción no permitida.');
        }
        $tipo = $this->request->getPost('tipo');
        if (! in_array($tipo, ['delivery', 'proveedor'], true)) {
            return redirect()->back()->withInput()->with('error', 'Tipo inválido.');
        }
        $casas  = $this->myCasas();
        $casaId = (int) $this->request->getPost('casa_id');
        if (! array_key_exists($casaId, $casas)) {
            return redirect()->back()->withInput()->with('error', 'Selecciona una de tus casas.');
        }

        [$desde, $hasta] = $this->vigencia();

        // Enforce the condominio's schedule against the arrival time.
        $condo = service('tenant')->active();
        $chk   = Horario::check($condo, $tipo, strtotime($desde));
        if ($chk['enforced'] && ! $chk['permitido']) {
            return redirect()->back()->withInput()->with('error', $chk['mensaje']);
        }

        $data = [
            'condominio_id'          => $this->activeCondominioId(),
            'casa_id'                => $casaId,
            'tipo'                   => $tipo,
            'solicitante_persona_id' => $persona['id'],
            'creado_por_user_id'     => auth()->id(),
            'nombre_visitante'       => $this->request->getPost('nombre_visitante'),
            'empresa'                => $this->request->getPost('empresa') ?: null,
            'num_personas'           => 1,
            // Vehicle/parking only applies to proveedores, not deliveries.
            'permite_vehiculo'       => ($tipo === 'proveedor' && $this->request->getPost('permite_vehiculo')) ? 1 : 0,
            'autoriza_cajon_propio'  => ($tipo === 'proveedor' && $this->request->getPost('autoriza_cajon_propio')) ? 1 : 0,
            'notas'                  => $this->request->getPost('notas') ?: null,
            'valido_desde'           => $desde,
            'valido_hasta'           => $hasta,
            'estado'                 => 'programado',
        ];
        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }
        $id = $this->model->getInsertID();
        (new AccesoEventoModel())->log($id, 'programado', null, auth()->id(), ucfirst($tipo) . ' esperado avisado por el residente');

        $acceso = $this->model->find($id);
        $nom    = trim($persona['nombre'] . ' ' . ($persona['apellido_paterno'] ?? ''));
        Notify::caseta($acceso, ucfirst($tipo) . ' esperado',
            $nom . ' avisa ' . $tipo . ' para ' . ($casas[$casaId] ?? '') . ': ' . $data['nombre_visitante'] . '.',
            'accesos/' . $id);

        return redirect()->to('portal/paquetes')->with('success', 'Avisaste a caseta. Cuando llegue, te darán acceso. ✅');
    }

    /** Packages/deliveries addressed to the resident (registered by caseta). */
    public function paquetes(): string|RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Tu usuario no está vinculado a una persona en este condominio.');
        }

        return view('paquetes/index', [
            'title'    => 'Paquetería y entregas',
            'paquetes' => $this->model->forSolicitante((int) $persona['id'], ['paqueteria', 'delivery', 'proveedor']),
        ]);
    }

    public function new(): string|RedirectResponse
    {
        if ($this->ownPersona() === null) {
            return redirect()->to('portal')->with('error', 'No puedes crear visitas: tu usuario no está vinculado a una persona.');
        }

        $casas = $this->myCasas();
        if ($casas === []) {
            return redirect()->to('portal')->with('error', 'No tienes casas asignadas para registrar visitas.');
        }

        return view('visitas/form', ['title' => 'Nueva visita', 'casas' => $casas]);
    }

    public function create(): RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Acción no permitida.');
        }

        $casaId = (int) $this->request->getPost('casa_id');
        if (! array_key_exists($casaId, $this->myCasas())) {
            return redirect()->back()->withInput()->with('error', 'Selecciona una de tus casas.');
        }

        [$desde, $hasta] = $this->vigencia();

        $data = [
            'condominio_id'          => $this->activeCondominioId(),
            'casa_id'                => $casaId,
            'tipo'                   => 'visita',
            'solicitante_persona_id' => $persona['id'],
            'creado_por_user_id'     => auth()->id(),
            'nombre_visitante'       => $this->request->getPost('nombre_visitante'),
            'empresa'                => $this->request->getPost('empresa') ?: null,
            'telefono'               => $this->request->getPost('telefono') ?: null,
            'num_personas'           => (int) ($this->request->getPost('num_personas') ?: 1),
            'placas'                 => $this->request->getPost('placas') ?: null,
            'permite_vehiculo'       => $this->request->getPost('permite_vehiculo') ? 1 : 0,
            'autoriza_cajon_propio'  => $this->request->getPost('autoriza_cajon_propio') ? 1 : 0,
            'notas'                  => $this->request->getPost('notas') ?: null,
            'qr_token'               => bin2hex(random_bytes(24)),
            'valido_desde'           => $desde,
            'valido_hasta'           => $hasta,
            'estado'                 => 'programado',
        ];

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        $id = $this->model->getInsertID();
        (new AccesoEventoModel())->log($id, 'programado', null, auth()->id(), 'Visita creada por el residente');

        return redirect()->to('portal/visitas/' . $id)->with('success', 'Visita creada. Comparte el QR con tu visitante.');
    }

    public function pase(int $id): string|RedirectResponse
    {
        $acceso = $this->ownVisita($id);
        if ($acceso === null) {
            return redirect()->to('portal/visitas')->with('error', 'Visita no encontrada.');
        }

        $casa = (new \App\Models\CasaModel())->find($acceso['casa_id']);

        return view('visitas/pase', [
            'title'     => 'Pase de visita',
            'acceso'    => $acceso,
            'casaIdent' => $casa['identificador'] ?? '',
            'passUrl'   => site_url('pase/' . $acceso['qr_token']),
        ]);
    }

    public function cancelar(int $id): RedirectResponse
    {
        $acceso = $this->ownVisita($id);
        if ($acceso !== null && in_array($acceso['estado'], ['programado'], true)) {
            $this->model->update($id, ['estado' => 'cancelado']);
            (new AccesoEventoModel())->log($id, 'cancelado', $acceso['estado'], auth()->id(), 'Cancelada por el residente');
        }

        return redirect()->to('portal/visitas')->with('success', 'Visita cancelada.');
    }

    /** A visit that belongs to the logged-in resident in the active condominio. */
    private function ownVisita(int $id): ?array
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return null;
        }

        return $this->model
            ->where('solicitante_persona_id', $persona['id'])
            ->where('condominio_id', $this->activeCondominioId())
            ->find($id);
    }

    /** The resident's casas (owned + occupied) in the active condominio. @return array<int,string> */
    private function myCasas(): array
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return [];
        }

        $casas = [];
        foreach ((new CasaPropietarioModel())->casasOfPersona((int) $persona['id']) as $c) {
            $casas[(int) $c['casa_id']] = $c['identificador'];
        }
        foreach ((new OcupanteModel())->casasForPersona((int) $persona['id']) as $c) {
            $casas[(int) $c['casa_id']] = $c['identificador'];
        }

        return $casas;
    }

    /** @return array{0:string,1:?string} [valido_desde, valido_hasta] in UTC */
    private function vigencia(): array
    {
        $now = date('Y-m-d H:i:s'); // server clock = UTC

        if ($this->request->getPost('vigencia') === 'programada') {
            $desde = $this->parseDateTime($this->request->getPost('valido_desde')) ?? $now;
            $hasta = $this->parseDateTime($this->request->getPost('valido_hasta'));

            return [$desde, $hasta];
        }

        // Immediate: valid now through end of *today in the user's/condominio's tz*, stored as UTC.
        try {
            $end = new \DateTime('now', new \DateTimeZone(Tz::current()));
            $end->setTime(23, 59, 59);
            $end->setTimezone(new \DateTimeZone('UTC'));
            $hasta = $end->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            $hasta = date('Y-m-d 23:59:59');
        }

        return [$now, $hasta];
    }

    /** Parse a datetime-local value (entered in the user's tz) into a UTC string. */
    private function parseDateTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            $dt = new \DateTime($value, new \DateTimeZone(Tz::current()));
            $dt->setTimezone(new \DateTimeZone('UTC'));

            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function ownPersona(): ?array
    {
        return $this->personas
            ->where('user_id', auth()->id())
            ->where('condominio_id', $this->activeCondominioId())
            ->first();
    }
}
