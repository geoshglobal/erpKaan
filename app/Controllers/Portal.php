<?php

namespace App\Controllers;

use App\Libraries\OccupantInvite;
use App\Models\CasaPropietarioModel;
use App\Models\InvitacionModel;
use App\Models\OcupacionModel;
use App\Models\OcupanteModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Resident portal: landing + self-service profile editing. Always operates on
 * the persona linked to the logged-in user within the active condominio.
 */
class Portal extends BaseController
{
    private PersonaModel $personas;

    public function __construct()
    {
        $this->personas = new PersonaModel();
    }

    public function index(): string
    {
        $persona = $this->ownPersona();

        $propiedades = [];
        $ocupaciones = [];
        if ($persona !== null) {
            $propiedades = (new CasaPropietarioModel())->casasOfPersona((int) $persona['id']);
            $ocupaciones = (new OcupanteModel())->casasForPersona((int) $persona['id']);
        }

        return view('portal/index', [
            'title'       => 'Mi portal',
            'persona'     => $persona,
            'propiedades' => $propiedades,
            'ocupaciones' => $ocupaciones,
        ]);
    }

    public function perfil(): string|RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Tu usuario no está vinculado a una persona en este condominio.');
        }

        return view('portal/perfil', [
            'title'   => 'Mi perfil',
            'persona' => $persona,
        ]);
    }

    public function updatePerfil(): RedirectResponse
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return redirect()->to('portal')->with('error', 'Persona no encontrada.');
        }

        if (! $this->validatePhoto()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->payload();
        if (($path = $this->storePhoto()) !== null) {
            $data['foto_path'] = $path;
            $this->deletePhoto($persona['foto_path'] ?? null);
        }

        if (! $this->personas->update($persona['id'], $data)) {
            return redirect()->back()->withInput()->with('errors', $this->personas->errors());
        }

        return redirect()->to('portal/perfil')->with('success', 'Tus datos se actualizaron.');
    }

    // ---- Principal manages occupants of their own casa -------------------

    public function ocupantes(int $ocupId): string|RedirectResponse
    {
        $oc = $this->principalOcupacion($ocupId);
        if ($oc === null) {
            return redirect()->to('portal')->with('error', 'No puedes gestionar los ocupantes de esa casa.');
        }

        $casa = (new \App\Models\CasaModel())->find($oc['casa_id']);

        return view('portal/ocupantes', [
            'title'       => 'Ocupantes',
            'ocupacion'   => $oc,
            'casa'        => $casa,
            'ocupantes'   => (new OcupanteModel())->ofOcupacion($ocupId),
            'invitaciones' => (new InvitacionModel())->pendingForOcupacion($ocupId),
        ]);
    }

    /** Principal adds an occupant by name only (no login account). */
    public function addOcupante(int $ocupId): RedirectResponse
    {
        $back = 'portal/ocupacion/' . $ocupId . '/ocupantes';
        $oc   = $this->principalOcupacion($ocupId);
        if ($oc === null) {
            return redirect()->to('portal')->with('error', 'Acción no permitida.');
        }

        $nombre = trim((string) $this->request->getPost('nombre'));
        if ($nombre === '') {
            return redirect()->to($back)->with('error', 'El nombre es obligatorio.');
        }

        $personaId = (int) $this->personas->insert([
            'condominio_id' => $this->activeCondominioId(),
            'nombre'        => $nombre,
            'activo'        => 1,
        ]);
        (new OccupantInvite())->addOccupant($ocupId, $personaId, 'secundario');

        return redirect()->to($back)->with('success', 'Ocupante agregado.');
    }

    /** Principal generates an account invitation for one of their occupants. */
    public function invitarOcupante(int $ocupId, int $ocupanteId): RedirectResponse
    {
        $back = 'portal/ocupacion/' . $ocupId . '/ocupantes';
        $oc   = $this->principalOcupacion($ocupId);
        if ($oc === null) {
            return redirect()->to('portal')->with('error', 'Acción no permitida.');
        }

        $ocupante = (new OcupanteModel())->where('ocupacion_id', $ocupId)->find($ocupanteId);
        if ($ocupante === null) {
            return redirect()->to($back)->with('error', 'Ocupante no encontrado.');
        }

        $persona = $this->personas->find($ocupante['persona_id']);
        if (! empty($persona['user_id'])) {
            return redirect()->to($back)->with('error', 'Ese ocupante ya tiene cuenta.');
        }

        // Resident-issued invite: roles are forced to non-privileged values.
        (new InvitacionModel())->insert([
            'condominio_id'      => $this->activeCondominioId(),
            'tipo'               => 'ocupante',
            'persona_id'         => (int) $persona['id'],
            'ocupacion_id'       => $ocupId,
            'token'              => bin2hex(random_bytes(24)),
            'rol'                => 'inquilino',
            'rol_ocupante'       => $ocupante['rol'] === 'principal' ? 'principal' : 'secundario',
            'nombre'             => PersonaModel::fullName($persona),
            'email'              => $persona['email'] ?: null,
            'expires_at'         => date('Y-m-d H:i:s', time() + 14 * 86400),
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()->to($back)->with('success', 'Invitación generada. Comparte el enlace con el ocupante.');
    }

    /** Principal removes a secondary occupant (never the principal, never self). */
    public function removeOcupante(int $ocupId, int $ocupanteId): RedirectResponse
    {
        $back = 'portal/ocupacion/' . $ocupId . '/ocupantes';
        $oc   = $this->principalOcupacion($ocupId);
        if ($oc === null) {
            return redirect()->to('portal')->with('error', 'Acción no permitida.');
        }

        $ocupanteModel = new OcupanteModel();
        $ocupante      = $ocupanteModel->where('ocupacion_id', $ocupId)->find($ocupanteId);
        if ($ocupante !== null && $ocupante['rol'] !== 'principal') {
            $ocupanteModel->delete($ocupanteId);
        }

        return redirect()->to($back)->with('success', 'Ocupante removido.');
    }

    /** The ocupación if the logged-in user is its principal in the active condominio, else null. */
    private function principalOcupacion(int $ocupId): ?array
    {
        $persona = $this->ownPersona();
        if ($persona === null) {
            return null;
        }
        $oc = (new OcupacionModel())->where('condominio_id', $this->activeCondominioId())->find($ocupId);
        if ($oc === null) {
            return null;
        }
        $isPrincipal = (new OcupanteModel())
            ->where('ocupacion_id', $ocupId)
            ->where('persona_id', $persona['id'])
            ->where('rol', 'principal')
            ->first();

        return $isPrincipal !== null ? $oc : null;
    }

    /** The logged-in user's persona within the active condominio. */
    private function ownPersona(): ?array
    {
        return $this->personas
            ->where('user_id', auth()->id())
            ->where('condominio_id', $this->activeCondominioId())
            ->first();
    }

    private function validatePhoto(): bool
    {
        return $this->validate([
            'foto' => 'permit_empty|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,3072]',
        ]);
    }

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

    /**
     * Self-editable fields only — never condominio_id, user_id or activo.
     *
     * @return array<string, mixed>
     */
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
        ];
    }
}
