<?php

namespace App\Controllers;

use App\Models\CasaModel;
use App\Models\OcupacionModel;
use App\Models\OcupanteModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Occupancy of a casa (ocupaciones) and its occupants (ocupantes), managed from
 * the casa. Tenant-scoped: the casa must belong to the active condominio.
 */
class Ocupaciones extends BaseController
{
    private CasaModel $casas;
    private PersonaModel $personas;
    private OcupacionModel $model;
    private OcupanteModel $ocupantes;

    public function __construct()
    {
        $this->casas     = new CasaModel();
        $this->personas  = new PersonaModel();
        $this->model     = new OcupacionModel();
        $this->ocupantes = new OcupanteModel();
    }

    public function index(int $casaId): string|RedirectResponse
    {
        $casa = $this->casaScoped($casaId);
        if ($casa === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        $ocupaciones = $this->model->forCasa($casaId);
        foreach ($ocupaciones as &$o) {
            $o['num_ocupantes'] = $this->ocupantes->countFor((int) $o['id']);
        }

        return view('ocupaciones/index', [
            'title'       => 'Ocupación — ' . $casa['identificador'],
            'casa'        => $casa,
            'ocupaciones' => $ocupaciones,
        ]);
    }

    public function new(int $casaId): string|RedirectResponse
    {
        $casa = $this->casaScoped($casaId);
        if ($casa === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        return view('ocupaciones/form', [
            'title'     => 'Nueva ocupación — ' . $casa['identificador'],
            'casa'      => $casa,
            'ocupacion' => null,
            'action'    => site_url('casas/' . $casaId . '/ocupaciones'),
            'ocupantes' => [],
            'personas'  => [],
        ]);
    }

    public function create(int $casaId): RedirectResponse
    {
        $casa = $this->casaScoped($casaId);
        if ($casa === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        $data                  = $this->payload();
        $data['casa_id']       = $casaId;
        $data['condominio_id'] = $this->activeCondominioId();

        if ((int) $data['vigente'] === 1) {
            $this->model->clearVigente($casaId);
        }

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        if ((int) $data['vigente'] === 1) {
            $this->syncCasaUso($casaId, $data['tipo_uso']);
        }

        $newId = $this->model->getInsertID();

        return redirect()->to('casas/' . $casaId . '/ocupaciones/' . $newId . '/editar')
            ->with('success', 'Ocupación creada. Ahora agrega los ocupantes.');
    }

    public function edit(int $casaId, int $ocupId): string|RedirectResponse
    {
        $casa      = $this->casaScoped($casaId);
        $ocupacion = $casa !== null ? $this->ocupacionScoped($casaId, $ocupId) : null;
        if ($ocupacion === null) {
            return redirect()->to('casas/' . $casaId . '/ocupaciones')->with('error', 'Ocupación no encontrada.');
        }

        return view('ocupaciones/form', [
            'title'     => 'Editar ocupación — ' . $casa['identificador'],
            'casa'      => $casa,
            'ocupacion' => $ocupacion,
            'action'    => site_url('casas/' . $casaId . '/ocupaciones/' . $ocupId),
            'ocupantes' => $this->ocupantes->ofOcupacion($ocupId),
            'personas'  => $this->personasList(),
        ]);
    }

    public function update(int $casaId, int $ocupId): RedirectResponse
    {
        $casa = $this->casaScoped($casaId);
        if ($casa === null || $this->ocupacionScoped($casaId, $ocupId) === null) {
            return redirect()->to('casas/' . $casaId . '/ocupaciones')->with('error', 'Ocupación no encontrada.');
        }

        $data = $this->payload();

        if ((int) $data['vigente'] === 1) {
            $this->model->clearVigente($casaId);
        }

        if (! $this->model->update($ocupId, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        if ((int) $data['vigente'] === 1) {
            $this->syncCasaUso($casaId, $data['tipo_uso']);
        }

        return redirect()->to('casas/' . $casaId . '/ocupaciones/' . $ocupId . '/editar')
            ->with('success', 'Ocupación actualizada.');
    }

    public function delete(int $casaId, int $ocupId): RedirectResponse
    {
        if ($this->casaScoped($casaId) !== null && $this->ocupacionScoped($casaId, $ocupId) !== null) {
            $this->model->delete($ocupId);
        }

        return redirect()->to('casas/' . $casaId . '/ocupaciones')->with('success', 'Ocupación eliminada.');
    }

    // ---- Ocupantes -------------------------------------------------------

    public function addOcupante(int $casaId, int $ocupId): RedirectResponse
    {
        $back = 'casas/' . $casaId . '/ocupaciones/' . $ocupId . '/editar';
        if ($this->casaScoped($casaId) === null || $this->ocupacionScoped($casaId, $ocupId) === null) {
            return redirect()->to('casas/' . $casaId . '/ocupaciones')->with('error', 'Ocupación no encontrada.');
        }

        $personaId = (int) $this->request->getPost('persona_id');
        if ($this->personaScoped($personaId) === null) {
            return redirect()->to($back)->with('error', 'Selecciona una persona válida.');
        }
        if ($this->ocupantes->isOcupante($ocupId, $personaId)) {
            return redirect()->to($back)->with('error', 'Esa persona ya es ocupante.');
        }

        $rol = $this->request->getPost('rol') === 'principal' ? 'principal' : 'secundario';
        if ($rol === 'principal') {
            $this->ocupantes->clearPrincipal($ocupId);
        }

        $this->ocupantes->save([
            'ocupacion_id' => $ocupId,
            'persona_id'   => $personaId,
            'rol'          => $rol,
            'parentesco'   => $this->request->getPost('parentesco') ?: null,
        ]);

        return redirect()->to($back)->with('success', 'Ocupante agregado.');
    }

    public function principalOcupante(int $casaId, int $ocupId, int $ocupanteId): RedirectResponse
    {
        if ($this->casaScoped($casaId) !== null
            && $this->ocupacionScoped($casaId, $ocupId) !== null
            && $this->ocupanteScoped($ocupId, $ocupanteId) !== null) {
            $this->ocupantes->clearPrincipal($ocupId);
            $this->ocupantes->update($ocupanteId, ['rol' => 'principal']);
        }

        return redirect()->to('casas/' . $casaId . '/ocupaciones/' . $ocupId . '/editar')
            ->with('success', 'Ocupante principal actualizado.');
    }

    public function removeOcupante(int $casaId, int $ocupId, int $ocupanteId): RedirectResponse
    {
        if ($this->casaScoped($casaId) !== null
            && $this->ocupacionScoped($casaId, $ocupId) !== null
            && $this->ocupanteScoped($ocupId, $ocupanteId) !== null) {
            $this->ocupantes->delete($ocupanteId);
        }

        return redirect()->to('casas/' . $casaId . '/ocupaciones/' . $ocupId . '/editar')
            ->with('success', 'Ocupante removido.');
    }

    // ---- Helpers ---------------------------------------------------------

    private function syncCasaUso(int $casaId, string $tipoUso): void
    {
        $this->casas->update($casaId, ['tipo_ocupacion_actual' => $tipoUso]);
    }

    private function casaScoped(int $casaId): ?array
    {
        return $this->casas->where('condominio_id', $this->activeCondominioId())->find($casaId);
    }

    private function ocupacionScoped(int $casaId, int $ocupId): ?array
    {
        return $this->model->where('casa_id', $casaId)->find($ocupId);
    }

    private function ocupanteScoped(int $ocupId, int $ocupanteId): ?array
    {
        return $this->ocupantes->where('ocupacion_id', $ocupId)->find($ocupanteId);
    }

    private function personaScoped(int $personaId): ?array
    {
        return $this->personas->where('condominio_id', $this->activeCondominioId())->find($personaId);
    }

    /** @return list<array<string,mixed>> */
    private function personasList(): array
    {
        return $this->personas
            ->where('condominio_id', $this->activeCondominioId())
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'tipo_uso'     => $this->request->getPost('tipo_uso') ?: 'propio',
            'fecha_inicio' => $this->request->getPost('fecha_inicio') ?: null,
            'fecha_fin'    => $this->request->getPost('fecha_fin') ?: null,
            'vigente'      => $this->request->getPost('vigente') !== null ? 1 : 0,
            'renta_monto'  => $this->request->getPost('renta_monto') ?: null,
            'deposito'     => $this->request->getPost('deposito') ?: null,
            'notas'        => $this->request->getPost('notas'),
        ];
    }
}
