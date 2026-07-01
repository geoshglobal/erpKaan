<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['kaan'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Active condominio id for the current user (tenant context), or null.
     */
    protected function activeCondominioId(): ?int
    {
        return service('tenant')->activeId();
    }

    /**
     * Resolve a date-range filter from `desde`/`hasta` GET params (local Y-m-d),
     * defaulting to the last N days. Returns the local dates (for the form) plus
     * `fecha_desde`/`fecha_hasta` as UTC datetime boundaries (for the query).
     *
     * @return array{desde:string,hasta:string,fecha_desde:?string,fecha_hasta:?string}
     */
    protected function dateRange(int $defaultDays = 15): array
    {
        $desde = trim((string) $this->request->getGet('desde'));
        $hasta = trim((string) $this->request->getGet('hasta'));
        if ($desde === '') {
            $desde = \App\Libraries\Tz::localDate($defaultDays);
        }
        if ($hasta === '') {
            $hasta = \App\Libraries\Tz::localDate(0);
        }

        return [
            'desde'       => $desde,
            'hasta'       => $hasta,
            'fecha_desde' => \App\Libraries\Tz::boundary($desde, false),
            'fecha_hasta' => \App\Libraries\Tz::boundary($hasta, true),
        ];
    }
}
