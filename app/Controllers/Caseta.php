<?php

namespace App\Controllers;

use App\Models\AccesoEventoModel;
use App\Models\AccesoModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Gate (caseta) operations: scan a QR and register entry/exit on an acceso.
 * Gated by caseta.operate and scoped to the operator's active condominio.
 */
class Caseta extends BaseController
{
    private AccesoModel $model;

    public function __construct()
    {
        $this->model = new AccesoModel();
    }

    public function escaner(): string
    {
        return view('caseta/escaner', ['title' => 'Escanear QR']);
    }

    public function checkin(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if (! in_array($acceso['estado'], ['programado', 'vencido'], true)) {
            return redirect()->back()->with('error', 'Este acceso no admite registro de entrada.');
        }

        $now = date('Y-m-d H:i:s');
        $this->model->update($id, [
            'estado'         => 'ingresado',
            'check_in_at'    => $now,
            'caseta_user_id' => auth()->id(),
        ]);
        (new AccesoEventoModel())->log($id, 'ingresado', $acceso['estado'], auth()->id(), 'Entrada registrada en caseta');

        return redirect()->back()->with('success', 'Entrada registrada. ✅');
    }

    public function checkout(int $id): RedirectResponse
    {
        $acceso = $this->scoped($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }
        if ($acceso['estado'] !== 'ingresado') {
            return redirect()->back()->with('error', 'Solo se registra salida de un acceso que ya ingresó.');
        }

        $this->model->update($id, [
            'estado'       => 'finalizado',
            'check_out_at' => date('Y-m-d H:i:s'),
        ]);
        (new AccesoEventoModel())->log($id, 'finalizado', 'ingresado', auth()->id(), 'Salida registrada en caseta');

        return redirect()->back()->with('success', 'Salida registrada. 👋');
    }

    /** Acceso restricted to the operator's active condominio. */
    private function scoped(int $id): ?array
    {
        return $this->model->where('condominio_id', $this->activeCondominioId())->find($id);
    }
}
