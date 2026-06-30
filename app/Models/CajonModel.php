<?php

namespace App\Models;

use CodeIgniter\Model;

class CajonModel extends Model
{
    protected $table          = 'cajones';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'casa_id', 'torre_id', 'identificador', 'tipo', 'techado', 'activo',
    ];

    protected $validationRules = [
        'condominio_id' => 'required|is_natural_no_zero',
        'casa_id'       => 'permit_empty|is_natural_no_zero',
        'torre_id'      => 'permit_empty|is_natural_no_zero',
        'identificador' => 'required|max_length[40]',
        'tipo'          => 'permit_empty|in_list[asignado,visita,comun]',
        'techado'       => 'permit_empty|in_list[0,1]',
        'activo'        => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'identificador' => ['required' => 'El identificador del cajón es obligatorio (ej. E-23).'],
    ];

    /**
     * Cajones of a condominio with the casa identifier (for listing).
     *
     * @return list<array<string, mixed>>
     */
    public function withCasa(int $condominioId): array
    {
        return $this->select('cajones.*, casas.identificador AS casa_ident')
            ->join('casas', 'casas.id = cajones.casa_id', 'left')
            ->where('cajones.condominio_id', $condominioId)
            ->orderBy('cajones.identificador', 'ASC')
            ->findAll();
    }
}
