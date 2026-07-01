<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificacionModel extends Model
{
    protected $table         = 'notificaciones';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $updatedField  = '';

    protected $allowedFields = [
        'condominio_id', 'persona_id', 'user_id', 'acceso_id',
        'tipo', 'titulo', 'mensaje', 'url', 'canal', 'leido_at',
        'email_enviado_at', 'email_error',
    ];

    /** Unread in-app notifications count for a user. */
    public function unreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)->where('leido_at', null)->countAllResults();
    }

    /** Latest notifications for a user. @return list<array<string,mixed>> */
    public function forUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)->orderBy('id', 'DESC')->findAll($limit);
    }

    public function markAllRead(int $userId): void
    {
        $this->builder()
            ->where('user_id', $userId)
            ->where('leido_at', null)
            ->update(['leido_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Mark the acceso notifications a persona received as corrected (an UPDATE, so
     * it works under prod privileges — no DELETE). Used when a mis-assigned
     * paquetería/registro is reassigned to another casa.
     */
    public function markCorrected(int $accesoId, int $personaId): void
    {
        $this->builder()
            ->where('acceso_id', $accesoId)
            ->where('persona_id', $personaId)
            ->update([
                'titulo'  => '⚠️ Corregido — no era para tu casa',
                'mensaje' => 'Este registro fue reasignado a otra vivienda. Puedes ignorarlo.',
                'url'     => null,
            ]);
    }
}
