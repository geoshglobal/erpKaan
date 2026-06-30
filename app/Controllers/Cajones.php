<?php

namespace App\Controllers;

use App\Models\CajonModel;
use App\Models\CasaModel;
use CodeIgniter\HTTP\RedirectResponse;

class Cajones extends BaseController
{
    private CajonModel $model;
    private CasaModel $casas;

    public function __construct()
    {
        $this->model = new CajonModel();
        $this->casas = new CasaModel();
    }

    public function index(): string
    {
        return view('cajones/index', [
            'title'   => 'Cajones',
            'cajones' => $this->model->withCasa((int) $this->activeCondominioId()),
        ]);
    }

    public function new(): string
    {
        return view('cajones/form', [
            'title'  => 'Nuevo cajón',
            'cajon'  => null,
            'casas'  => $this->casasList(),
            'action' => site_url('cajones'),
        ]);
    }

    public function create(): RedirectResponse
    {
        $data                  = $this->payload();
        $data['condominio_id'] = $this->activeCondominioId();

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('cajones')->with('success', 'Cajón creado.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $cajon = $this->findScoped($id);
        if ($cajon === null) {
            return redirect()->to('cajones')->with('error', 'Cajón no encontrado.');
        }

        return view('cajones/form', [
            'title'  => 'Editar cajón',
            'cajon'  => $cajon,
            'casas'  => $this->casasList(),
            'action' => site_url('cajones/' . $id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->findScoped($id) === null) {
            return redirect()->to('cajones')->with('error', 'Cajón no encontrado.');
        }

        if (! $this->model->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('cajones')->with('success', 'Cajón actualizado.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->findScoped($id) !== null) {
            $this->model->delete($id);
        }

        return redirect()->to('cajones')->with('success', 'Cajón eliminado.');
    }

    /** Find a cajon ensuring it belongs to the active condominio. */
    private function findScoped(int $id): ?array
    {
        return $this->model->where('condominio_id', $this->activeCondominioId())->find($id);
    }

    /** Casas of the active condominio for the select. @return list<array<string,mixed>> */
    private function casasList(): array
    {
        return $this->casas
            ->select('id, identificador')
            ->where('condominio_id', $this->activeCondominioId())
            ->orderBy('identificador', 'ASC')
            ->findAll();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        $casaId    = (int) $this->request->getPost('casa_id');
        $validCasa = $casaId > 0
            && $this->casas->where('condominio_id', $this->activeCondominioId())->find($casaId) !== null;

        $techado = $this->request->getPost('techado');

        return [
            'casa_id'       => $validCasa ? $casaId : null,
            'identificador' => $this->request->getPost('identificador'),
            'tipo'          => $this->request->getPost('tipo') ?: 'asignado',
            'techado'       => ($techado === '0' || $techado === '1') ? (int) $techado : null,
            'activo'        => (int) ($this->request->getPost('activo') ?? 1),
        ];
    }
}
