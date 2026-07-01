<?php

namespace App\Libraries;

use App\Models\CasaModel;
use App\Models\CasaPropietarioModel;
use App\Models\OcupacionModel;
use App\Models\OcupanteModel;

/**
 * Resolves the residents (owners + current occupants) of a casa — used to pick
 * the recipient (destinatario) when caseta registers a package/delivery that no
 * resident pre-registered. Prefers people with a login account so notifications
 * (in-app/push) reach them.
 */
class CasaResidents
{
    /**
     * People linked to a casa, deduped by persona, principal first.
     * @return list<array{id:int,nombre:string,principal:bool}>
     */
    public static function forCasa(int $casaId): array
    {
        $out = [];

        // Current (vigente) occupants — these carry a user_id when they have an account.
        foreach ((new OcupacionModel())->forCasa($casaId) as $o) {
            if (empty($o['vigente'])) {
                continue;
            }
            foreach ((new OcupanteModel())->ofOcupacion((int) $o['id']) as $oc) {
                $pid = (int) $oc['persona_id'];
                $out[$pid] ??= [
                    'id'        => $pid,
                    'nombre'    => trim($oc['nombre'] . ' ' . ($oc['apellido_paterno'] ?? '')),
                    'principal' => $oc['rol'] === 'principal',
                    'rank'      => $oc['rol'] === 'principal' ? 0 : 1,
                ];
            }
        }

        // Owners (fallback / additional recipients).
        foreach ((new CasaPropietarioModel())->ownersOfCasa($casaId) as $ow) {
            $pid = (int) $ow['persona_id'];
            if (isset($out[$pid])) {
                continue;
            }
            $out[$pid] = [
                'id'        => $pid,
                'nombre'    => trim($ow['nombre'] . ' ' . ($ow['apellido_paterno'] ?? '')),
                'principal' => ! empty($ow['principal']),
                'rank'      => ! empty($ow['principal']) ? 2 : 3,
            ];
        }

        usort($out, static fn ($a, $b) => $a['rank'] <=> $b['rank']);

        return array_map(static fn ($r) => ['id' => $r['id'], 'nombre' => $r['nombre'] ?: 'Sin nombre', 'principal' => $r['principal']], $out);
    }

    /** Best default recipient persona id for a casa (principal first), or null. */
    public static function defaultRecipient(int $casaId): ?int
    {
        $list = self::forCasa($casaId);

        return $list[0]['id'] ?? null;
    }

    /**
     * Map casaId => residents for every casa of a condominio (for the caseta
     * registration form's destinatario picker).
     * @return array<int, list<array{id:int,nombre:string,principal:bool}>>
     */
    public static function mapForCondominio(int $condominioId): array
    {
        $map = [];
        $casas = (new CasaModel())->where('condominio_id', $condominioId)->orderBy('identificador', 'ASC')->findAll();
        foreach ($casas as $casa) {
            $map[(int) $casa['id']] = self::forCasa((int) $casa['id']);
        }

        return $map;
    }
}
