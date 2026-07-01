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
        $cid   = (int) $this->activeCondominioId();
        $range = $this->dateRange(15);
        $filters = [
            'casa_id'     => (int) $this->request->getGet('casa_id'),
            'tipo'        => (string) $this->request->getGet('tipo'),
            'estado'      => (string) $this->request->getGet('estado'),
            'q'           => trim((string) $this->request->getGet('q')),
            'desde'       => $range['desde'],
            'hasta'       => $range['hasta'],
            'fecha_desde' => $range['fecha_desde'],
            'fecha_hasta' => $range['fecha_hasta'],
        ];

        $accesos = $this->model->scopeForCondominio($cid, $filters)->paginate(20);
        $pager   = $this->model->pager;
        $pager->only(['casa_id', 'tipo', 'estado', 'q', 'desde', 'hasta']);

        $casas = (new CasaModel())->where('condominio_id', $cid)->orderBy('identificador', 'ASC')->findAll();

        return view('accesos/index', [
            'title'   => 'Accesos',
            'accesos' => $accesos,
            'pager'   => $pager,
            'casas'   => $casas,
            'filters' => $filters,
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
