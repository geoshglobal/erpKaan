<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonaModel extends Model
{
    protected $table          = 'personas';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'user_id',
        'nombre', 'apellido_paterno', 'apellido_materno',
        'email', 'telefono', 'telefono2', 'foto_path', 'fecha_nacimiento',
        'rfc', 'razon_social', 'regimen_fiscal', 'uso_cfdi', 'cp_fiscal',
        'notas', 'activo',
    ];

    protected $validationRules = [
        'condominio_id'    => 'required|is_natural_no_zero',
        'nombre'           => 'required|max_length[120]',
        'apellido_paterno' => 'permit_empty|max_length[120]',
        'apellido_materno' => 'permit_empty|max_length[120]',
        'email'            => 'permit_empty|valid_email|max_length[150]',
        'telefono'         => 'permit_empty|max_length[30]',
        'telefono2'        => 'permit_empty|max_length[30]',
        'fecha_nacimiento' => 'permit_empty|valid_date[Y-m-d]',
        'rfc'              => 'permit_empty|max_length[13]',
        'activo'           => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nombre' => ['required' => 'El nombre es obligatorio.'],
        'email'  => ['valid_email' => 'El correo no tiene un formato válido.'],
    ];

    /** Full display name from the parts. */
    public static function fullName(array $persona): string
    {
        return trim(sprintf(
            '%s %s %s',
            $persona['nombre'] ?? '',
            $persona['apellido_paterno'] ?? '',
            $persona['apellido_materno'] ?? ''
        ));
    }
}
