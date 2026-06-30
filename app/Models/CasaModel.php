<?php

namespace App\Models;

use CodeIgniter\Model;

class CasaModel extends Model
{
    protected $table          = 'casas';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'torre_id', 'identificador', 'tipo_ocupacion_actual',
        'num_cajones', 'max_ocupantes', 'm2', 'notas', 'activo',
    ];

    protected $validationRules = [
        'condominio_id'         => 'required|is_natural_no_zero',
        'torre_id'              => 'permit_empty|is_natural_no_zero',
        'identificador'         => 'required|max_length[40]',
        'tipo_ocupacion_actual' => 'permit_empty|in_list[propio,renta_lineal,renta_vacacional]',
        'num_cajones'           => 'permit_empty|is_natural',
        'm2'                    => 'permit_empty|decimal',
        'activo'                => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'identificador' => ['required' => 'El identificador de la casa es obligatorio (ej. A-101).'],
    ];

    /**
     * Casas of a condominio joined with their torre name (for listing).
     *
     * @return list<array<string, mixed>>
     */
    public function withTorre(int $condominioId): array
    {
        return $this->select('casas.*, torres.nombre AS torre_nombre')
            ->join('torres', 'torres.id = casas.torre_id', 'left')
            ->where('casas.condominio_id', $condominioId)
            ->orderBy('casas.identificador', 'ASC')
            ->findAll();
    }
}
