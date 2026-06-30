<?php

namespace App\Controllers;

use App\Libraries\OccupantInvite;
use App\Libraries\ResidentAccount;
use App\Models\InvitacionModel;
use App\Models\PersonaModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Public, token-gated registration. Handles two invitation kinds:
 *  - tipo 'cuenta'   : create a login for an existing persona (F2.0).
 *  - tipo 'ocupante' : create/link a resident and attach them to a casa's
 *                      ocupación; if the email already has an account, the
 *                      user must prove identity (password) and choose add/move.
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

        if ($inv['tipo'] === 'ocupante') {
            return view('registro/ocupante_form', [
                'token'   => $token,
                'inv'     => $inv,
                'persona' => $inv['persona_id'] ? $this->personas->find($inv['persona_id']) : null,
                'errors'  => [],
            ]);
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

        return $inv['tipo'] === 'ocupante'
            ? $this->registerOcupante($inv, $token)
            : $this->registerCuenta($inv, $token);
    }

    /** Original F2.0 flow: account for an existing persona. */
    private function registerCuenta(array $inv, string $token): RedirectResponse
    {
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
        $this->signIn($result['user']);

        return redirect()->to('portal')->with('success', '¡Cuenta creada! Bienvenido a erpKaan.');
    }

    /** Occupant flow: new account or claim an existing one, then attach to the casa. */
    private function registerOcupante(array $inv, string $token): RedirectResponse|string
    {
        $email    = trim(strtolower((string) ($this->request->getPost('email') ?: $inv['email'])));
        $nombre   = $this->request->getPost('nombre') ?: $inv['nombre'];
        $telefono = $this->request->getPost('telefono') ?: null;

        $existing = $email !== '' ? (new UserModel())->findByCredentials(['email' => $email]) : null;

        // --- Brand-new account ------------------------------------------------
        if ($existing === null) {
            $password = (string) $this->request->getPost('password');
            $confirm  = (string) $this->request->getPost('password_confirm');
            $errors   = [];
            if (! $nombre) {
                $errors[] = 'El nombre es obligatorio.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Las contraseñas no coinciden.';
            }
            if ($errors !== []) {
                return $this->ocupanteForm($token, $inv, $errors);
            }

            $result = (new OccupantInvite())->acceptAsNewUser($inv, $nombre, $email, $telefono, $password);
            if (! $result['ok']) {
                return $this->ocupanteForm($token, $inv, $result['errors']);
            }

            $this->invitaciones->update($inv['id'], ['used_at' => date('Y-m-d H:i:s')]);
            $this->signIn($result['user']);

            return redirect()->to('portal')->with('success', '¡Cuenta creada! Te vinculamos a tu casa.');
        }

        // --- Existing email: must prove identity, then choose add/move --------
        $mode = $this->request->getPost('mode');
        if ($mode === null) {
            return view('registro/ocupante_claim', [
                'token' => $token, 'inv' => $inv, 'email' => $email,
                'nombre' => $nombre, 'telefono' => $telefono, 'errors' => [],
            ]);
        }

        $password = (string) $this->request->getPost('password');
        $cred     = auth()->check(['email' => $email, 'password' => $password]);
        if (! $cred->isOK()) {
            return view('registro/ocupante_claim', [
                'token' => $token, 'inv' => $inv, 'email' => $email,
                'nombre' => $nombre, 'telefono' => $telefono,
                'errors' => ['La contraseña no es correcta.'],
            ]);
        }

        $user = $cred->extraInfo();
        $mode = $mode === 'mudar' ? 'mudar' : 'agregar';
        (new OccupantInvite())->acceptAsExistingUser($inv, $user, $mode, $nombre, $telefono);

        $this->invitaciones->update($inv['id'], ['used_at' => date('Y-m-d H:i:s')]);
        $this->signIn($user);

        $msg = $mode === 'mudar' ? 'Te mudamos a la nueva casa.' : 'Te agregamos a la nueva casa.';

        return redirect()->to('portal')->with('success', $msg);
    }

    private function ocupanteForm(string $token, array $inv, array $errors): string
    {
        return view('registro/ocupante_form', [
            'token'   => $token,
            'inv'     => $inv,
            'persona' => $inv['persona_id'] ? $this->personas->find($inv['persona_id']) : null,
            'errors'  => $errors,
        ]);
    }

    /** Sign in as the given user, replacing any current session. */
    private function signIn($user): void
    {
        if (auth()->loggedIn()) {
            auth()->logout();
        }
        auth()->login($user);
    }
}
