<?php

namespace App\Controllers;

use App\Models\CondominioModel;
use CodeIgniter\HTTP\RedirectResponse;

class Condominios extends BaseController
{
    private CondominioModel $model;

    public function __construct()
    {
        $this->model = new CondominioModel();
    }

    public function index(): string
    {
        return view('condominios/index', [
            'title'       => 'Condominios',
            'condominios' => $this->model->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function new(): string
    {
        return view('condominios/form', [
            'title'      => 'Nuevo condominio',
            'condominio' => null,
            'action'     => site_url('condominios'),
        ]);
    }

    public function create(): RedirectResponse
    {
        if (! $this->model->save($this->payload())) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('condominios')->with('success', 'Condominio creado correctamente.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $condominio = $this->model->find($id);
        if ($condominio === null) {
            return redirect()->to('condominios')->with('error', 'Condominio no encontrado.');
        }

        return view('condominios/form', [
            'title'      => 'Editar condominio',
            'condominio' => $condominio,
            'action'     => site_url('condominios/' . $id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->model->find($id) === null) {
            return redirect()->to('condominios')->with('error', 'Condominio no encontrado.');
        }

        if (! $this->model->update($id, $this->payload())) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('condominios')->with('success', 'Condominio actualizado.');
    }

    public function delete(int $id): RedirectResponse
    {
        $this->model->delete($id); // soft delete (sets deleted_at)

        return redirect()->to('condominios')->with('success', 'Condominio eliminado.');
    }

    /**
     * Switch the active condominio (tenant context). Allowed for any logged-in
     * user; the Tenant service rejects ids the user may not access.
     */
    public function setActivo(): RedirectResponse
    {
        $id = (int) $this->request->getPost('condominio_id');

        service('tenant')->setActive($id);

        return redirect()->back();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'nombre'         => $this->request->getPost('nombre'),
            'slug'           => $this->request->getPost('slug'),
            'direccion'      => $this->request->getPost('direccion'),
            'colonia'        => $this->request->getPost('colonia'),
            'municipio'      => $this->request->getPost('municipio'),
            'estado'         => $this->request->getPost('estado'),
            'cp'             => $this->request->getPost('cp'),
            'pais'           => $this->request->getPost('pais') ?: 'MX',
            'moneda'         => $this->request->getPost('moneda') ?: 'MXN',
            'telefono'       => $this->request->getPost('telefono'),
            'email'          => $this->request->getPost('email'),
            'razon_social'   => $this->request->getPost('razon_social'),
            'rfc'            => $this->request->getPost('rfc'),
            'regimen_fiscal' => $this->request->getPost('regimen_fiscal'),
            'cp_fiscal'      => $this->request->getPost('cp_fiscal'),
            'activo'         => $this->request->getPost('activo') !== null ? 1 : 0,
        ];
    }
}
