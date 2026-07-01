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

        return view('caseta/checkin', [
            'title'         => 'Registrar entrada',
            'acceso'        => $acceso,
            'solicitante'   => $acceso['solicitante_persona_id'] ? (new PersonaModel())->find($acceso['solicitante_persona_id']) : null,
            'cajonesLibres' => $parking->availableVisitorSpots((int) $this->activeCondominioId()),
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

        // Parking assignment for vehicles.
        $needAuth = false;
        if ($vehiculo) {
            $parking = new \App\Libraries\Parking();
            $cajonId = (int) $this->request->getPost('cajon_id');
            if ($cajonId && $parking->isFreeVisitorSpot((int) $this->activeCondominioId(), $cajonId)) {
                $data['cajon_id']           = $cajonId;
                $data['autorizacion_cajon'] = null;
            } elseif ($this->request->getPost('usar_cajon_residente')) {
                $data['autorizacion_cajon'] = 'pendiente';
                $needAuth                   = true;
            }
        }

        $this->model->update($id, $data);

        $nota = 'Entrada. Pax: ' . $data['pax_ingresaron']
            . ($vehiculo ? ', vehículo' . ($data['folio_corbatin'] ? ' folio ' . $data['folio_corbatin'] : '') : '')
            . ($sinId ? ', sin ID' : '');
        (new AccesoEventoModel())->log($id, 'ingresado', $acceso['estado'], auth()->id(), $nota);

        Notify::acceso(
            $acceso,
            'Tu visita llegó',
            $acceso['nombre_visitante'] . ' ingresó al condominio' . ($vehiculo ? ' en vehículo' : '') . '.'
        );

        if ($needAuth) {
            Notify::acceso(
                $acceso,
                'Autoriza el uso de tu cajón',
                'No hay cajones de visita disponibles para ' . $acceso['nombre_visitante']
                . '. Autoriza o rechaza el uso de tu cajón en “Mi portal → Autorizaciones”.'
            );
        }

        $msg = $needAuth
            ? 'Entrada registrada. Se solicitó autorización del residente para el cajón. ⏳'
            : 'Entrada registrada. ✅';

        return redirect()->to('accesos/' . $id)->with('success', $msg);
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

        Notify::acceso($acceso, 'Tu visita salió', $acceso['nombre_visitante'] . ' salió del condominio.');

        return redirect()->to('accesos/' . $id)->with('success', 'Salida registrada. 👋');
    }

    private function storeIdFoto(): ?string
    {
        $file = $this->request->getFile('id_foto');
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
