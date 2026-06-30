<?php

namespace App\Controllers;

use App\Libraries\ResidentAccount;
use App\Models\InvitacionModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Resident login accounts, managed from a persona (admin only). Supports both
 * creating the account directly and generating a per-persona invitation link.
 */
class Cuentas extends BaseController
{
    private PersonaModel $personas;
    private InvitacionModel $invitaciones;

    public function __construct()
    {
        $this->personas     = new PersonaModel();
        $this->invitaciones = new InvitacionModel();
    }

    public function index(int $personaId): string|RedirectResponse
    {
        $persona = $this->personaScoped($personaId);
        if ($persona === null) {
            return redirect()->to('personas')->with('error', 'Persona no encontrada.');
        }

        $account = null;
        if (! empty($persona['user_id'])) {
            $account = (new UserModel())->findById($persona['user_id']);
        }

        return view('cuentas/index', [
            'title'      => 'Acceso a la app — ' . PersonaModel::fullName($persona),
            'persona'    => $persona,
            'account'    => $account,
            'invitacion' => $this->invitaciones->pendingForPersona($personaId),
            'roles'      => ResidentAccount::ROLES,
        ]);
    }

    public function store(int $personaId): RedirectResponse
    {
        $persona = $this->personaScoped($personaId);
        if ($persona === null) {
            return redirect()->to('personas')->with('error', 'Persona no encontrada.');
        }

        $result = ResidentAccount::create(
            $persona,
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('password'),
            (string) $this->request->getPost('rol'),
        );

        if (! $result['ok']) {
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }

        return redirect()->to('personas/' . $personaId . '/cuenta')
            ->with('success', 'Cuenta creada. El residente ya puede iniciar sesión con su correo.');
    }

    public function invitar(int $personaId): RedirectResponse
    {
        $persona = $this->personaScoped($personaId);
        if ($persona === null) {
            return redirect()->to('personas')->with('error', 'Persona no encontrada.');
        }
        if (! empty($persona['user_id'])) {
            return redirect()->back()->with('error', 'Esta persona ya tiene cuenta.');
        }

        $rol = (string) $this->request->getPost('rol');
        if (! in_array($rol, ResidentAccount::ROLES, true)) {
            return redirect()->back()->with('error', 'Selecciona un rol válido.');
        }

        $token = bin2hex(random_bytes(24));
        $this->invitaciones->insert([
            'condominio_id'      => $this->activeCondominioId(),
            'persona_id'         => $personaId,
            'token'              => $token,
            'rol'                => $rol,
            'email'              => $persona['email'] ?: null,
            'expires_at'         => date('Y-m-d H:i:s', time() + 14 * 86400), // 14 days
            'created_by_user_id' => auth()->id(),
        ]);

        return redirect()->to('personas/' . $personaId . '/cuenta')
            ->with('success', 'Invitación generada. Comparte el enlace con el residente.');
    }

    private function personaScoped(int $personaId): ?array
    {
        return $this->personas->where('condominio_id', $this->activeCondominioId())->find($personaId);
    }
}
