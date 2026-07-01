<?php

namespace App\Models;

use CodeIgniter\Model;

class AccesoModel extends Model
{
    protected $table          = 'accesos';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'condominio_id', 'casa_id', 'tipo', 'solicitante_persona_id', 'creado_por_user_id',
        'nombre_visitante', 'empresa', 'telefono', 'num_personas', 'pax_ingresaron',
        'placas', 'permite_vehiculo', 'autoriza_cajon_propio', 'ingreso_vehiculo', 'folio_corbatin', 'cajon_id',
        'autorizacion_cajon', 'foto_path', 'foto_entrega_path', 'id_foto_path', 'sin_id', 'id_nota',
        'qr_token', 'valido_desde', 'valido_hasta', 'estado',
        'check_in_at', 'check_out_at', 'caseta_user_id', 'notas',
    ];

    protected $validationRules = [
        'condominio_id'    => 'required|is_natural_no_zero',
        'casa_id'          => 'required|is_natural_no_zero',
        'tipo'             => 'permit_empty|in_list[visita,paqueteria,delivery,proveedor]',
        'nombre_visitante' => 'required|max_length[150]',
        'num_personas'     => 'permit_empty|is_natural_no_zero',
        'valido_desde'     => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'valido_hasta'     => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];

    public const ESTADOS = [
        'programado' => 'Programado',
        'ingresado'  => 'Ingresó',
        'finalizado' => 'Finalizó',
        'en_caseta'  => 'En caseta',
        'entregado'  => 'Entregado',
        'cancelado'  => 'Cancelado',
        'vencido'    => 'Vencido',
    ];

    public const TIPOS = [
        'visita'     => 'Visita',
        'paqueteria' => 'Paquetería',
        'delivery'   => 'Delivery',
        'proveedor'  => 'Proveedor',
    ];

    public function byToken(string $token): ?array
    {
        return $this->where('qr_token', $token)->first();
    }

    /** All accesos of a condominio with casa + requester name (supervision). @return list<array<string,mixed>> */
    public function forCondominio(int $condominioId): array
    {
        return $this->select('accesos.*, casas.identificador AS casa_ident,
                TRIM(CONCAT(personas.nombre, " ", COALESCE(personas.apellido_paterno, ""))) AS solicitante')
            ->join('casas', 'casas.id = accesos.casa_id', 'left')
            ->join('personas', 'personas.id = accesos.solicitante_persona_id', 'left')
            ->where('accesos.condominio_id', $condominioId)
            ->orderBy('accesos.id', 'DESC')
            ->findAll();
    }

    /**
     * Accesos requested for / addressed to a persona (most recent first),
     * optionally filtered by tipo(s).
     * @param list<string>|null $tipos
     * @return list<array<string,mixed>>
     */
    public function forSolicitante(int $personaId, ?array $tipos = null): array
    {
        $q = $this->select('accesos.*, casas.identificador AS casa_ident')
            ->join('casas', 'casas.id = accesos.casa_id', 'left')
            ->where('accesos.solicitante_persona_id', $personaId);
        if ($tipos !== null) {
            $q->whereIn('accesos.tipo', $tipos);
        }

        return $q->orderBy('accesos.id', 'DESC')->findAll();
    }

    /**
     * Display status, deriving "vencido" for still-programmed visits whose
     * validity window has passed (without mutating the stored row).
     */
    public static function estadoEfectivo(array $acceso): string
    {
        if ($acceso['estado'] === 'programado'
            && ! empty($acceso['valido_hasta'])
            && strtotime($acceso['valido_hasta']) < time()) {
            return 'vencido';
        }

        return $acceso['estado'];
    }
}
