<?php

namespace App\Models;

use CodeIgniter\Model;

class OcupacionModel extends Model
{
    protected $table          = 'ocupaciones';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'casa_id', 'tipo_uso', 'fecha_inicio', 'fecha_fin',
        'vigente', 'renta_monto', 'deposito', 'notas',
    ];

    protected $validationRules = [
        'condominio_id' => 'required|is_natural_no_zero',
        'casa_id'       => 'required|is_natural_no_zero',
        'tipo_uso'      => 'permit_empty|in_list[propio,renta_lineal,renta_vacacional]',
        'fecha_inicio'  => 'permit_empty|valid_date[Y-m-d]',
        'fecha_fin'     => 'permit_empty|valid_date[Y-m-d]',
        'vigente'       => 'permit_empty|in_list[0,1]',
        'renta_monto'   => 'permit_empty|decimal',
        'deposito'      => 'permit_empty|decimal',
    ];

    /**
     * Ocupaciones of a casa, current first then most recent.
     *
     * @return list<array<string, mixed>>
     */
    public function forCasa(int $casaId): array
    {
        return $this->where('casa_id', $casaId)
            ->orderBy('vigente', 'DESC')
            ->orderBy('fecha_inicio', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /** Clear the vigente flag on all ocupaciones of a casa. */
    public function clearVigente(int $casaId): void
    {
        $this->builder()
            ->where('casa_id', $casaId)
            ->where('deleted_at', null)
            ->update(['vigente' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
