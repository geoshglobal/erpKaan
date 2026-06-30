<?php

namespace App\Controllers;

use App\Models\CasaModel;
use App\Models\CasaPropietarioModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Manage owners (casa_propietarios) from a casa. Tenant-scoped: the casa must
 * belong to the active condominio, and owners are picked from its personas.
 */
class Propietarios extends BaseController
{
    private CasaModel $casas;
    private PersonaModel $personas;
    private CasaPropietarioModel $model;

    public function __construct()
    {
        $this->casas    = new CasaModel();
        $this->personas = new PersonaModel();
        $this->model    = new CasaPropietarioModel();
    }

    public function index(int $casaId): string|RedirectResponse
    {
        $casa = $this->casaScoped($casaId);
        if ($casa === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        return view('propietarios/index', [
            'title'    => 'Propietarios — ' . $casa['identificador'],
            'casa'     => $casa,
            'owners'   => $this->model->ownersOfCasa($casaId),
            'personas' => $this->personasList(),
        ]);
    }

    public function store(int $casaId): RedirectResponse
    {
        $casa = $this->casaScoped($casaId);
        if ($casa === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        $personaId = (int) $this->request->getPost('persona_id');
        if ($this->personaScoped($personaId) === null) {
            return redirect()->back()->with('error', 'Selecciona una persona válida.');
        }
        if ($this->model->isOwner($casaId, $personaId)) {
            return redirect()->back()->with('error', 'Esa persona ya es propietaria de esta casa.');
        }

        $principal = $this->request->getPost('principal') !== null ? 1 : 0;
        if ($principal === 1) {
            $this->model->clearPrincipal($casaId);
        }

        $data = [
            'casa_id'      => $casaId,
            'persona_id'   => $personaId,
            'principal'    => $principal,
            'porcentaje'   => $this->request->getPost('porcentaje') ?: 100,
            'fecha_inicio' => $this->request->getPost('fecha_inicio') ?: null,
            'fecha_fin'    => $this->request->getPost('fecha_fin') ?: null,
        ];

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('casas/' . $casaId . '/propietarios')->with('success', 'Propietario agregado.');
    }

    public function principal(int $casaId, int $id): RedirectResponse
    {
        if ($this->casaScoped($casaId) !== null && $this->linkScoped($casaId, $id) !== null) {
            $this->model->clearPrincipal($casaId);
            $this->model->update($id, ['principal' => 1]);
        }

        return redirect()->to('casas/' . $casaId . '/propietarios')->with('success', 'Propietario principal actualizado.');
    }

    public function destroy(int $casaId, int $id): RedirectResponse
    {
        if ($this->casaScoped($casaId) !== null && $this->linkScoped($casaId, $id) !== null) {
            $this->model->delete($id);
        }

        return redirect()->to('casas/' . $casaId . '/propietarios')->with('success', 'Propietario removido.');
    }

    /** Casa restricted to the active condominio. */
    private function casaScoped(int $casaId): ?array
    {
        return $this->casas->where('condominio_id', $this->activeCondominioId())->find($casaId);
    }

    /** Persona restricted to the active condominio. */
    private function personaScoped(int $personaId): ?array
    {
        return $this->personas->where('condominio_id', $this->activeCondominioId())->find($personaId);
    }

    /** Ownership link restricted to the given casa. */
    private function linkScoped(int $casaId, int $id): ?array
    {
        return $this->model->where('casa_id', $casaId)->find($id);
    }

    /** Personas of the active condominio for the select. @return list<array<string,mixed>> */
    private function personasList(): array
    {
        return $this->personas
            ->where('condominio_id', $this->activeCondominioId())
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }
}
