<?php

namespace App\Controllers;

use App\Libraries\ResidentAccount;
use App\Models\InvitacionModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Public, token-gated resident self-registration via a per-persona invitation.
 */
class Registro extends BaseController
{
    private InvitacionModel $invitaciones;
    private PersonaModel $personas;

    public function __construct()
    {
        $this->invitaciones = new InvitacionModel();
        $this->personas     = new PersonaModel();
    }

    public function show(string $token): string
    {
        $inv = $this->invitaciones->findValidByToken($token);
        if ($inv === null) {
            return view('registro/invalid');
        }

        return view('registro/form', [
            'token'   => $token,
            'inv'     => $inv,
            'persona' => $this->personas->find($inv['persona_id']),
        ]);
    }

    public function register(string $token): RedirectResponse|string
    {
        $inv = $this->invitaciones->findValidByToken($token);
        if ($inv === null) {
            return view('registro/invalid');
        }

        $persona  = $this->personas->find($inv['persona_id']);
        $email    = trim((string) ($this->request->getPost('email') ?: $inv['email']));
        $password = (string) $this->request->getPost('password');
        $confirm  = (string) $this->request->getPost('password_confirm');

        if ($password !== $confirm) {
            return redirect()->back()->withInput()->with('errors', ['Las contraseñas no coinciden.']);
        }

        $result = ResidentAccount::create($persona, $email, $password, $inv['rol']);
        if (! $result['ok']) {
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }

        $this->invitaciones->update($inv['id'], ['used_at' => date('Y-m-d H:i:s')]);

        // Drop any existing session (e.g. an admin testing the link) before
        // signing in as the new resident — Shield forbids login while logged in.
        if (auth()->loggedIn()) {
            auth()->logout();
        }
        auth()->login($result['user']); // auto sign-in

        return redirect()->to('portal')->with('success', '¡Cuenta creada! Bienvenido a erpKaan.');
    }
}
