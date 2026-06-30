<?php

namespace App\Models;

use CodeIgniter\Model;

class CondominioModel extends Model
{
    protected $table            = 'condominios';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'nombre', 'slug', 'direccion', 'colonia', 'municipio', 'estado', 'cp',
        'pais', 'moneda', 'telefono', 'email',
        'razon_social', 'rfc', 'regimen_fiscal', 'cp_fiscal',
        'latitud', 'longitud',
        'logo_path', 'settings', 'activo',
    ];

    protected $validationRules = [
        'id'       => 'permit_empty|is_natural_no_zero',
        'nombre'   => 'required|max_length[150]',
        'slug'     => 'permit_empty|alpha_dash|max_length[160]|is_unique[condominios.slug,id,{id}]',
        'email'    => 'permit_empty|valid_email|max_length[150]',
        'rfc'      => 'permit_empty|max_length[13]',
        'cp'       => 'permit_empty|max_length[10]',
        'pais'     => 'permit_empty|exact_length[2]',
        'moneda'   => 'permit_empty|exact_length[3]',
        'latitud'  => 'permit_empty|decimal',
        'longitud' => 'permit_empty|decimal',
        'activo'   => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nombre' => ['required' => 'El nombre del condominio es obligatorio.'],
        'slug'   => ['is_unique' => 'Ya existe un condominio con ese identificador (slug).'],
    ];

    protected $beforeInsert = ['ensureSlug'];
    protected $beforeUpdate = ['ensureSlug'];

    /**
     * Auto-generate a unique slug from the nombre when none is provided.
     */
    protected function ensureSlug(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        $fields = $data['data'];
        $hasSlug = isset($fields['slug']) && trim((string) $fields['slug']) !== '';

        if (! $hasSlug && ! empty($fields['nombre'])) {
            $base = url_title($fields['nombre'], '-', true);
            $slug = $base;
            $i    = 1;

            while ($this->where('slug', $slug)
                ->where('id !=', $data['id'][0] ?? 0)
                ->withDeleted()
                ->first() !== null) {
                $slug = $base . '-' . (++$i);
            }

            $data['data']['slug'] = $slug;
        }

        return $data;
    }
}
