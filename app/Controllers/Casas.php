<?php

namespace App\Controllers;

use App\Models\CasaModel;
use App\Models\TorreModel;
use CodeIgniter\HTTP\RedirectResponse;

class Casas extends BaseController
{
    private CasaModel $model;
    private TorreModel $torres;

    public function __construct()
    {
        $this->model  = new CasaModel();
        $this->torres = new TorreModel();
    }

    public function index(): string
    {
        return view('casas/index', [
            'title' => 'Casas',
            'casas' => $this->model->withTorre((int) $this->activeCondominioId()),
        ]);
    }

    public function new(): string
    {
        return view('casas/form', [
            'title'  => 'Nueva casa',
            'casa'   => null,
            'torres' => $this->torresList(),
            'action' => site_url('casas'),
        ]);
    }

    public function create(): RedirectResponse
    {
        $data                  = $this->payload();
        $data['condominio_id'] = $this->activeCondominioId();

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('casas')->with('success', 'Casa creada.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $casa = $this->findScoped($id);
        if ($casa === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        return view('casas/form', [
            'title'  => 'Editar casa',
            'casa'   => $casa,
            'torres' => $this->torresList(),
            'action' => site_url('casas/' . $id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->findScoped($id) === null) {
            return redirect()->to('casas')->with('error', 'Casa no encontrada.');
        }

        if (! $this->model->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('casas')->with('success', 'Casa actualizada.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->findScoped($id) !== null) {
            $this->model->delete($id);
        }

        return redirect()->to('casas')->with('success', 'Casa eliminada.');
    }

    /** Find a casa ensuring it belongs to the active condominio. */
    private function findScoped(int $id): ?array
    {
        return $this->model->where('condominio_id', $this->activeCondominioId())->find($id);
    }

    /** Torres of the active condominio, for the select. @return list<array<string,mixed>> */
    private function torresList(): array
    {
        return $this->torres
            ->select('id, nombre')
            ->where('condominio_id', $this->activeCondominioId())
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        $torreId    = (int) $this->request->getPost('torre_id');
        $validTorre = $torreId > 0
            && $this->torres->where('condominio_id', $this->activeCondominioId())->find($torreId) !== null;

        return [
            'torre_id'              => $validTorre ? $torreId : null,
            'identificador'         => $this->request->getPost('identificador'),
            'tipo_ocupacion_actual' => $this->request->getPost('tipo_ocupacion_actual') ?: 'propio',
            'num_cajones'           => (int) ($this->request->getPost('num_cajones') ?? 0),
            'm2'                    => $this->request->getPost('m2') ?: null,
            'notas'                 => $this->request->getPost('notas'),
            'activo'                => (int) ($this->request->getPost('activo') ?? 1),
        ];
    }
}
