<?php

namespace App\Libraries;

use App\Models\PersonaModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

/**
 * Creates Shield login accounts for residents and links them to a persona.
 * Shared by the admin "create account" flow and the invitation self-register flow.
 */
class ResidentAccount
{
    public const ROLES = ['dueno', 'inquilino', 'huesped'];

    /**
     * Create a login for a persona.
     *
     * @return array{ok: bool, errors: list<string>, user: ?User}
     */
    public static function create(array $persona, string $email, string $password, string $rol): array
    {
        $errors = [];
        $email  = trim(strtolower($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo no tiene un formato válido.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (! in_array($rol, self::ROLES, true)) {
            $errors[] = 'El rol seleccionado no es válido.';
        }
        if (! empty($persona['user_id'])) {
            $errors[] = 'Esta persona ya tiene una cuenta.';
        }

        $users = new UserModel();
        if ($errors === [] && $users->findByCredentials(['email' => $email]) !== null) {
            $errors[] = 'Ese correo ya está registrado por otro usuario.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'user' => null];
        }

        $user = new User([
            'username' => self::uniqueUsername($email, $users),
            'email'    => $email,
            'password' => $password,
        ]);
        $users->save($user);

        $user = $users->findById($users->getInsertID());
        $user->activate();
        $user->addGroup($rol);

        (new PersonaModel())->update((int) $persona['id'], ['user_id' => $user->id]);

        return ['ok' => true, 'errors' => [], 'user' => $user];
    }

    private static function uniqueUsername(string $email, UserModel $users): string
    {
        $base = preg_replace('/[^a-z0-9._-]/', '', strtolower(explode('@', $email)[0])) ?: 'residente';
        $name = $base;
        $i    = 1;
        while ($users->where('username', $name)->first() !== null) {
            $name = $base . (++$i);
        }

        return $name;
    }
}
