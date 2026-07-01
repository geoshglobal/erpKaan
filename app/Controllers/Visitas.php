<?php

namespace App\Controllers;

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
            'visitas' => $this->model->forSolicitante((int) $persona['id']),
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

    /** @return array{0:string,1:?string} [valido_desde, valido_hasta] */
    private function vigencia(): array
    {
        $now = date('Y-m-d H:i:s');

        if ($this->request->getPost('vigencia') === 'programada') {
            $desde = $this->parseDateTime($this->request->getPost('valido_desde')) ?? $now;
            $hasta = $this->parseDateTime($this->request->getPost('valido_hasta'));

            return [$desde, $hasta];
        }

        // Immediate: valid now through end of the day.
        return [$now, date('Y-m-d 23:59:59')];
    }

    private function parseDateTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $ts = strtotime($value);

        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function ownPersona(): ?array
    {
        return $this->personas
            ->where('user_id', auth()->id())
            ->where('condominio_id', $this->activeCondominioId())
            ->first();
    }
}
