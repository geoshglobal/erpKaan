<?php

namespace App\Models;

use CodeIgniter\Model;

class AccesoEventoModel extends Model
{
    protected $table         = 'acceso_eventos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $updatedField  = '';

    protected $allowedFields = ['acceso_id', 'estado_anterior', 'estado_nuevo', 'user_id', 'nota'];

    /** Record a status change on an acceso. */
    public function log(int $accesoId, string $nuevo, ?string $anterior = null, ?int $userId = null, ?string $nota = null): void
    {
        $this->insert([
            'acceso_id'       => $accesoId,
            'estado_anterior' => $anterior,
            'estado_nuevo'    => $nuevo,
            'user_id'         => $userId,
            'nota'            => $nota,
        ]);
    }

    /** Timeline for an acceso (oldest first). @return list<array<string,mixed>> */
    public function forAcceso(int $accesoId): array
    {
        return $this->where('acceso_id', $accesoId)->orderBy('id', 'ASC')->findAll();
    }
}
