<?php

namespace App\Models;

use CodeIgniter\Model;

class InvitacionModel extends Model
{
    protected $table          = 'invitaciones';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'persona_id', 'token', 'rol', 'email',
        'expires_at', 'used_at', 'created_by_user_id',
    ];

    /** A still-usable invitation by token (not used, not expired), or null. */
    public function findValidByToken(string $token): ?array
    {
        $row = $this->where('token', $token)->where('used_at', null)->first();
        if ($row === null) {
            return null;
        }
        if ($row['expires_at'] !== null && strtotime($row['expires_at']) < time()) {
            return null;
        }

        return $row;
    }

    /** The latest pending invitation for a persona, or null. */
    public function pendingForPersona(int $personaId): ?array
    {
        return $this->where('persona_id', $personaId)
            ->where('used_at', null)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
