<?php

namespace App\Libraries;

use App\Models\CasaModel;
use App\Models\CondominioModel;
use App\Models\InvitacionModel;
use App\Models\OcupacionModel;
use App\Models\OcupanteModel;

/**
 * Per-condominio / per-casa occupancy rules. Today: max occupants per casa
 * (casa override falls back to the condominio default; NULL = unlimited).
 */
class OccupancyRules
{
    /** Effective max occupants for a casa, or null if unlimited. */
    public function limitForCasa(int $casaId): ?int
    {
        $casa = (new CasaModel())->find($casaId);
        if ($casa === null) {
            return null;
        }
        if ($casa['max_ocupantes'] !== null && $casa['max_ocupantes'] !== '') {
            return (int) $casa['max_ocupantes'];
        }

        $condo = (new CondominioModel())->find($casa['condominio_id']);

        return ($condo && $condo['max_ocupantes'] !== null && $condo['max_ocupantes'] !== '')
            ? (int) $condo['max_ocupantes']
            : null;
    }

    /**
     * Whether adding $adding more occupants to an ocupación would exceed the casa
     * limit, counting current occupants plus pending occupant invitations.
     */
    public function wouldExceed(int $ocupacionId, int $adding = 1): bool
    {
        $ocupacion = (new OcupacionModel())->find($ocupacionId);
        if ($ocupacion === null) {
            return false;
        }

        $limit = $this->limitForCasa((int) $ocupacion['casa_id']);
        if ($limit === null) {
            return false; // unlimited
        }

        $current = (new OcupanteModel())->countFor($ocupacionId);
        $pending = (new InvitacionModel())
            ->where('ocupacion_id', $ocupacionId)
            ->where('tipo', 'ocupante')
            ->where('used_at', null)
            ->countAllResults();

        return ($current + $pending + $adding) > $limit;
    }
}
