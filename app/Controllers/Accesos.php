<?php

namespace App\Controllers;

use App\Models\AccesoEventoModel;
use App\Models\AccesoModel;
use App\Models\CasaModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Supervision panel: all accesos of the active condominio (read-only here;
 * caseta check-in/out lands in F2.2). Gated by permission accesos.supervisar,
 * so supervisors (superadmin/admin/comité/caseta) can oversee without owning a
 * property. NOT persona-scoped — this is condominio-wide.
 */
class Accesos extends BaseController
{
    private AccesoModel $model;

    public function __construct()
    {
        $this->model = new AccesoModel();
    }

    public function index(): string
    {
        return view('accesos/index', [
            'title'   => 'Accesos',
            'accesos' => $this->model->forCondominio((int) $this->activeCondominioId()),
        ]);
    }

    public function detail(int $id): string|RedirectResponse
    {
        $acceso = $this->model
            ->where('condominio_id', $this->activeCondominioId())
            ->find($id);
        if ($acceso === null) {
            return redirect()->to('accesos')->with('error', 'Acceso no encontrado.');
        }

        $casa = (new CasaModel())->find($acceso['casa_id']);

        return view('accesos/detail', [
            'title'    => 'Acceso',
            'acceso'   => $acceso,
            'casa'     => $casa,
            'eventos'  => (new AccesoEventoModel())->forAcceso($id),
        ]);
    }
}
