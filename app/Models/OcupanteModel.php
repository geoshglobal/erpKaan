<?php

namespace App\Models;

use CodeIgniter\Model;

class OcupanteModel extends Model
{
    protected $table          = 'ocupantes';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'ocupacion_id', 'persona_id', 'rol', 'parentesco',
    ];

    protected $validationRules = [
        'ocupacion_id' => 'required|is_natural_no_zero',
        'persona_id'   => 'required|is_natural_no_zero',
        'rol'          => 'permit_empty|in_list[principal,secundario]',
        'parentesco'   => 'permit_empty|max_length[60]',
    ];

    /**
     * Ocupantes of an ocupacion joined with persona name parts (principal first).
     *
     * @return list<array<string, mixed>>
     */
    public function ofOcupacion(int $ocupacionId): array
    {
        return $this->select('ocupantes.*, personas.nombre, personas.apellido_paterno, personas.apellido_materno, personas.foto_path')
            ->join('personas', 'personas.id = ocupantes.persona_id')
            ->where('ocupantes.ocupacion_id', $ocupacionId)
            ->orderBy("FIELD(ocupantes.rol, 'principal', 'secundario')", '', false)
            ->orderBy('personas.nombre', 'ASC')
            ->findAll();
    }

    public function isOcupante(int $ocupacionId, int $personaId): bool
    {
        return $this->where('ocupacion_id', $ocupacionId)->where('persona_id', $personaId)->first() !== null;
    }

    public function countFor(int $ocupacionId): int
    {
        return $this->where('ocupacion_id', $ocupacionId)->countAllResults();
    }

    /**
     * Casas a persona currently occupies (vigente occupancies), with identifier and role.
     *
     * @return list<array<string, mixed>>
     */
    public function casasForPersona(int $personaId): array
    {
        return $this->select('ocupaciones.id AS ocupacion_id, casas.id AS casa_id, casas.identificador, ocupantes.rol, ocupaciones.tipo_uso')
            ->join('ocupaciones', 'ocupaciones.id = ocupantes.ocupacion_id')
            ->join('casas', 'casas.id = ocupaciones.casa_id')
            ->where('ocupantes.persona_id', $personaId)
            ->where('ocupaciones.vigente', 1)
            ->where('ocupaciones.deleted_at', null)
            ->orderBy('casas.identificador', 'ASC')
            ->findAll();
    }

    /** Clear the principal role (set to secundario) for all ocupantes of an ocupacion. */
    public function clearPrincipal(int $ocupacionId): void
    {
        $this->builder()
            ->where('ocupacion_id', $ocupacionId)
            ->where('deleted_at', null)
            ->update(['rol' => 'secundario', 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
