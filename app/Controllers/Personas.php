<?php

namespace App\Controllers;

use App\Models\CasaPropietarioModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

class Personas extends BaseController
{
    private PersonaModel $model;

    public function __construct()
    {
        $this->model = new PersonaModel();
    }

    public function index(): string
    {
        return view('personas/index', [
            'title'    => 'Personas',
            'personas' => $this->model->where('condominio_id', $this->activeCondominioId())
                ->orderBy('nombre', 'ASC')->findAll(),
        ]);
    }

    public function new(): string
    {
        return view('personas/form', [
            'title'   => 'Nueva persona',
            'persona' => null,
            'action'  => site_url('personas'),
        ]);
    }

    public function create(): RedirectResponse
    {
        if (! $this->validatePhoto()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data                  = $this->payload();
        $data['condominio_id'] = $this->activeCondominioId();

        if (($path = $this->storePhoto()) !== null) {
            $data['foto_path'] = $path;
        }

        if (! $this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('personas')->with('success', 'Persona registrada.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $persona = $this->findScoped($id);
        if ($persona === null) {
            return redirect()->to('personas')->with('error', 'Persona no encontrada.');
        }

        return view('personas/form', [
            'title'       => 'Editar persona',
            'persona'     => $persona,
            'action'      => site_url('personas/' . $id),
            'propiedades' => (new CasaPropietarioModel())->casasOfPersona($id),
        ]);
    }

    public function update(int $id): RedirectResponse
    {
        $persona = $this->findScoped($id);
        if ($persona === null) {
            return redirect()->to('personas')->with('error', 'Persona no encontrada.');
        }

        if (! $this->validatePhoto()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload();

        if (($path = $this->storePhoto()) !== null) {
            $data['foto_path'] = $path;
            $this->deletePhoto($persona['foto_path'] ?? null);
        }

        if (! $this->model->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to('personas')->with('success', 'Persona actualizada.');
    }

    public function delete(int $id): RedirectResponse
    {
        $persona = $this->findScoped($id);
        if ($persona !== null) {
            $this->model->delete($id); // soft delete; keep the photo file
        }

        return redirect()->to('personas')->with('success', 'Persona eliminada.');
    }

    /** Find a persona ensuring it belongs to the active condominio. */
    private function findScoped(int $id): ?array
    {
        return $this->model->where('condominio_id', $this->activeCondominioId())->find($id);
    }

    private function validatePhoto(): bool
    {
        return $this->validate([
            'foto' => 'permit_empty|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,3072]',
        ]);
    }

    /** Move the uploaded photo if present; returns the web path or null. */
    private function storePhoto(): ?string
    {
        $file = $this->request->getFile('foto');
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $name = $file->getRandomName();
        $file->move(FCPATH . 'uploads/personas', $name);

        return 'uploads/personas/' . $name;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'uploads/personas/') && is_file(FCPATH . $path)) {
            @unlink(FCPATH . $path);
        }
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'nombre'           => $this->request->getPost('nombre'),
            'apellido_paterno' => $this->request->getPost('apellido_paterno'),
            'apellido_materno' => $this->request->getPost('apellido_materno'),
            'email'            => $this->request->getPost('email'),
            'telefono'         => $this->request->getPost('telefono'),
            'telefono2'        => $this->request->getPost('telefono2'),
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?: null,
            'rfc'              => $this->request->getPost('rfc'),
            'razon_social'     => $this->request->getPost('razon_social'),
            'regimen_fiscal'   => $this->request->getPost('regimen_fiscal'),
            'uso_cfdi'         => $this->request->getPost('uso_cfdi'),
            'cp_fiscal'        => $this->request->getPost('cp_fiscal'),
            'notas'            => $this->request->getPost('notas'),
            'activo'           => (int) ($this->request->getPost('activo') ?? 1),
        ];
    }
}
