<?php

namespace App\Controllers;

use App\Models\TorreModel;
use CodeIgniter\HTTP\RedirectResponse;

class Torres extends BaseController
{
    private TorreModel $model;

    public function __construct()
    {
        $this->model = new TorreModel();
    }

    public function index(): string
    {
        $condoId = $this->activeCondominioId();

        return view('torres/index', [
            'title'  => 'Torres',
            'torres' => $this->model->where('condominio_id', $condoId)
                ->orderBy('orden', 'ASC')->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function new(): string
    {
        return view('torres/form', [
            'title'  => 'Nueva torre',
            'torre'  => null,
            'action' => site_url('torres'),
        ]);
    }

    public function create(): RedirectResponse
    {
        $data                  = $this->payload();
        $data['condominio_id'] = $this->activeCondominioId();

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('torres')->with('success', 'Torre creada.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $torre = $this->findScoped($id);
        if ($torre === null) {
            return redirect()->to('torres')->with('error', 'Torre no encontrada.');
        }

        return view('torres/form', [
            'title'  => 'Editar torre',
            'torre'  => $torre,
            'action' => site_url('torres/' . $id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->findScoped($id) === null) {
            return redirect()->to('torres')->with('error', 'Torre no encontrada.');
        }

        if (! $this->model->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('torres')->with('success', 'Torre actualizada.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->findScoped($id) !== null) {
            $this->model->delete($id);
        }

        return redirect()->to('torres')->with('success', 'Torre eliminada.');
    }

    /** Find a torre ensuring it belongs to the active condominio (tenant isolation). */
    private function findScoped(int $id): ?array
    {
        return $this->model
            ->where('condominio_id', $this->activeCondominioId())
            ->find($id);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'nombre'      => $this->request->getPost('nombre'),
            'clave'       => $this->request->getPost('clave'),
            'descripcion' => $this->request->getPost('descripcion'),
            'orden'       => (int) ($this->request->getPost('orden') ?? 0),
            'activo'      => (int) ($this->request->getPost('activo') ?? 1),
        ];
    }
}
