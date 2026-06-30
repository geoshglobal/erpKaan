<?php

namespace App\Models;

use CodeIgniter\Model;

class TorreModel extends Model
{
    protected $table          = 'torres';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'clave', 'nombre', 'descripcion', 'orden', 'activo',
    ];

    protected $validationRules = [
        'condominio_id' => 'required|is_natural_no_zero',
        'nombre'        => 'required|max_length[120]',
        'clave'         => 'permit_empty|max_length[20]',
        'orden'         => 'permit_empty|is_natural',
        'activo'        => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nombre' => ['required' => 'El nombre de la torre es obligatorio.'],
    ];
}
