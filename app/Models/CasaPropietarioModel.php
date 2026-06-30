<?php

namespace App\Models;

use CodeIgniter\Model;

class CasaPropietarioModel extends Model
{
    protected $table          = 'casa_propietarios';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'casa_id', 'persona_id', 'principal', 'porcentaje', 'fecha_inicio', 'fecha_fin',
    ];

    protected $validationRules = [
        'casa_id'      => 'required|is_natural_no_zero',
        'persona_id'   => 'required|is_natural_no_zero',
        'principal'    => 'permit_empty|in_list[0,1]',
        'porcentaje'   => 'permit_empty|decimal|greater_than[0]|less_than_equal_to[100]',
        'fecha_inicio' => 'permit_empty|valid_date[Y-m-d]',
        'fecha_fin'    => 'permit_empty|valid_date[Y-m-d]',
    ];

    /**
     * Owners of a casa joined with persona name parts (active links only).
     *
     * @return list<array<string, mixed>>
     */
    public function ownersOfCasa(int $casaId): array
    {
        return $this->select('casa_propietarios.*, personas.nombre, personas.apellido_paterno, personas.apellido_materno, personas.foto_path')
            ->join('personas', 'personas.id = casa_propietarios.persona_id')
            ->where('casa_propietarios.casa_id', $casaId)
            ->orderBy('casa_propietarios.principal', 'DESC')
            ->orderBy('personas.nombre', 'ASC')
            ->findAll();
    }

    /**
     * Casas owned by a persona, joined with the casa identifier.
     *
     * @return list<array<string, mixed>>
     */
    public function casasOfPersona(int $personaId): array
    {
        return $this->select('casa_propietarios.*, casas.identificador')
            ->join('casas', 'casas.id = casa_propietarios.casa_id')
            ->where('casa_propietarios.persona_id', $personaId)
            ->orderBy('casas.identificador', 'ASC')
            ->findAll();
    }

    /** Whether a persona is already an active owner of the casa. */
    public function isOwner(int $casaId, int $personaId): bool
    {
        return $this->where('casa_id', $casaId)->where('persona_id', $personaId)->first() !== null;
    }

    /** Clear the principal flag on all owners of a casa (before setting a new one). */
    public function clearPrincipal(int $casaId): void
    {
        $this->builder()
            ->where('casa_id', $casaId)
            ->where('deleted_at', null)
            ->update(['principal' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
