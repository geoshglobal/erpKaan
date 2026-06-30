<?php

namespace App\Libraries;

use App\Models\OcupanteModel;
use App\Models\PersonaModel;
use CodeIgniter\Shield\Entities\User;

/**
 * Links an accepted occupant invitation to a casa's ocupación, resolving the
 * persona (new or existing) and the login account, and applying the add/move
 * choice for residents who already occupy other casas in the same condominio.
 *
 * Security: callers must pass an invitation that was issued by an authorized
 * party; the role lives on the invitation, never on user input.
 */
class OccupantInvite
{
    private PersonaModel $personas;
    private OcupanteModel $ocupantes;

    public function __construct()
    {
        $this->personas  = new PersonaModel();
        $this->ocupantes = new OcupanteModel();
    }

    /**
     * Accept an occupant invitation for a brand-new account (email not yet used).
     *
     * @return array{ok: bool, errors: list<string>, user: ?User}
     */
    public function acceptAsNewUser(array $inv, string $nombre, string $email, ?string $telefono, string $password): array
    {
        $personaId = $inv['persona_id'] ? (int) $inv['persona_id'] : null;

        if ($personaId === null) {
            $personaId = (int) $this->personas->insert([
                'condominio_id' => $inv['condominio_id'],
                'nombre'        => $nombre,
                'email'         => $email,
                'telefono'      => $telefono,
                'activo'        => 1,
            ]);
        }

        $persona = $this->personas->find($personaId);

        $result = ResidentAccount::create($persona, $email, $password, $inv['rol']);
        if (! $result['ok']) {
            return $result;
        }

        $this->addOccupant((int) $inv['ocupacion_id'], $personaId, $inv['rol_ocupante'] ?? 'secundario');

        return $result;
    }

    /**
     * Accept for an already-registered user (identity proven by password elsewhere).
     * $mode: 'agregar' keeps existing occupancies; 'mudar' ends the user's other
     * occupancies in this condominio first.
     *
     * @return array{ok: bool, errors: list<string>}
     */
    public function acceptAsExistingUser(array $inv, User $user, string $mode, ?string $nombreFallback, ?string $telefono): array
    {
        $condominioId = (int) $inv['condominio_id'];

        $persona = $this->personas
            ->where('user_id', $user->id)
            ->where('condominio_id', $condominioId)
            ->first();

        if ($persona === null) {
            // The user is new to THIS condominio: create a persona linked to them.
            $existing = $this->personas->where('user_id', $user->id)->first();
            $pid      = (int) $this->personas->insert([
                'condominio_id' => $condominioId,
                'user_id'       => $user->id,
                'nombre'        => $nombreFallback ?: ($existing['nombre'] ?? $user->username),
                'email'         => $user->email,
                'telefono'      => $telefono,
                'activo'        => 1,
            ]);
            $persona = $this->personas->find($pid);
        }

        if (! $user->inGroup($inv['rol'])) {
            $user->addGroup($inv['rol']);
        }

        if ($mode === 'mudar') {
            $this->endOtherOccupancies($user, $condominioId, (int) $inv['ocupacion_id']);
        }

        $this->addOccupant((int) $inv['ocupacion_id'], (int) $persona['id'], $inv['rol_ocupante'] ?? 'secundario');

        return ['ok' => true, 'errors' => []];
    }

    /** Add a persona as occupant of an ocupación (idempotent; enforces single principal). */
    public function addOccupant(int $ocupacionId, int $personaId, string $rolOcupante): void
    {
        if ($this->ocupantes->isOcupante($ocupacionId, $personaId)) {
            return;
        }
        $rol = $rolOcupante === 'principal' ? 'principal' : 'secundario';
        if ($rol === 'principal') {
            $this->ocupantes->clearPrincipal($ocupacionId);
        }
        $this->ocupantes->insert([
            'ocupacion_id' => $ocupacionId,
            'persona_id'   => $personaId,
            'rol'          => $rol,
        ]);
    }

    /** Soft-delete the user's ocupante rows in this condominio except the target ocupación. */
    private function endOtherOccupancies(User $user, int $condominioId, int $keepOcupacionId): void
    {
        $personaIds = array_column(
            $this->personas->select('id')->where('user_id', $user->id)->where('condominio_id', $condominioId)->findAll(),
            'id'
        );
        if ($personaIds === []) {
            return;
        }

        $rows = $this->ocupantes
            ->whereIn('persona_id', $personaIds)
            ->where('ocupacion_id !=', $keepOcupacionId)
            ->findAll();

        foreach ($rows as $row) {
            $this->ocupantes->delete($row['id']);
        }
    }
}
