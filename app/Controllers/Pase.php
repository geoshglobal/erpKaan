<?php

namespace App\Controllers;

use App\Models\AccesoModel;
use App\Models\CasaModel;

/**
 * Public, token-addressed visit pass. This is what the QR opens; in F2.2 the
 * caseta panel will validate/check-in from here. Read-only for now.
 */
class Pase extends BaseController
{
    public function show(string $token): string
    {
        $acceso = (new AccesoModel())->byToken($token);
        if ($acceso === null) {
            return view('pase/invalid');
        }

        $casa = (new CasaModel())->find($acceso['casa_id']);

        return view('pase/show', [
            'title'     => 'Pase de acceso',
            'acceso'    => $acceso,
            'casaIdent' => $casa['identificador'] ?? '',
            'passUrl'   => site_url('pase/' . $acceso['qr_token']),
        ]);
    }
}
