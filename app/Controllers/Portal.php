<?php

namespace App\Controllers;

use App\Models\CasaPropietarioModel;
use App\Models\OcupanteModel;
use App\Models\PersonaModel;

/**
 * Resident portal landing. Base for F2.1+ (visits, parcels) hung off here.
 */
class Portal extends BaseController
{
    public function index(): string
    {
        $user    = auth()->user();
        $persona = (new PersonaModel())->where('user_id', $user->id)->first();

        $propiedades = [];
        $ocupaciones = [];
        if ($persona !== null) {
            $propiedades = (new CasaPropietarioModel())->casasOfPersona((int) $persona['id']);
            $ocupaciones = (new OcupanteModel())->casasForPersona((int) $persona['id']);
        }

        return view('portal/index', [
            'title'       => 'Mi portal',
            'persona'     => $persona,
            'propiedades' => $propiedades,
            'ocupaciones' => $ocupaciones,
        ]);
    }
}
