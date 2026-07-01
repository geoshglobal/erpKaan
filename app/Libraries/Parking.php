<?php

namespace App\Libraries;

use App\Models\CajonModel;

/**
 * Visitor parking availability. A visitor spot (cajon tipo='visita') is occupied
 * while an acceso that is currently inside (estado='ingresado') holds it via
 * accesos.cajon_id.
 */
class Parking
{
    /** Visitor spots (tipo='visita') of a condominio. @return list<array<string,mixed>> */
    public function visitorSpots(int $condominioId): array
    {
        return (new CajonModel())
            ->where('condominio_id', $condominioId)
            ->where('tipo', 'visita')
            ->where('activo', 1)
            ->orderBy('identificador', 'ASC')
            ->findAll();
    }

    /** Cajon ids currently held by accesos that are inside. @return list<int> */
    public function occupiedSpotIds(int $condominioId): array
    {
        $rows = db_connect()->table('accesos')
            ->select('cajon_id')
            ->where('condominio_id', $condominioId)
            ->where('estado', 'ingresado')
            ->where('cajon_id IS NOT NULL', null, false)
            ->where('deleted_at', null)
            ->get()->getResultArray();

        return array_map(static fn ($r): int => (int) $r['cajon_id'], $rows);
    }

    /** Visitor spots that are free right now. @return list<array<string,mixed>> */
    public function availableVisitorSpots(int $condominioId): array
    {
        $occupied = $this->occupiedSpotIds($condominioId);

        return array_values(array_filter(
            $this->visitorSpots($condominioId),
            static fn (array $c): bool => ! in_array((int) $c['id'], $occupied, true)
        ));
    }

    /** Whether a given cajon id is a free visitor spot of the condominio. */
    public function isFreeVisitorSpot(int $condominioId, int $cajonId): bool
    {
        foreach ($this->availableVisitorSpots($condominioId) as $c) {
            if ((int) $c['id'] === $cajonId) {
                return true;
            }
        }

        return false;
    }
}
