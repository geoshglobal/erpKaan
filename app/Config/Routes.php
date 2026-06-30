<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Shield auth routes (login, register, logout, magic-link, etc.)
service('auth')->routes($routes);

// Public resident self-registration via per-persona invitation token.
$routes->get('registro/(:segment)', 'Registro::show/$1');
$routes->post('registro/(:segment)', 'Registro::register/$1');

// Public visit pass (what the QR opens).
$routes->get('pase/(:segment)', 'Pase::show/$1');

// Authenticated area
$routes->group('', ['filter' => 'session'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('portal', 'Portal::index');
    $routes->get('notificaciones', 'Notificaciones::index');
    $routes->get('portal/perfil', 'Portal::perfil');
    $routes->post('portal/perfil', 'Portal::updatePerfil');
    // Resident visits (tipo=visita) + QR pass.
    $routes->get('portal/visitas', 'Visitas::index');
    $routes->get('portal/visitas/nueva', 'Visitas::new');
    $routes->post('portal/visitas', 'Visitas::create');
    $routes->get('portal/visitas/(:num)', 'Visitas::pase/$1');
    $routes->post('portal/visitas/(:num)/cancelar', 'Visitas::cancelar/$1');
    // Principal manages occupants of their own casa.
    $routes->get('portal/ocupacion/(:num)/ocupantes', 'Portal::ocupantes/$1');
    $routes->post('portal/ocupacion/(:num)/ocupantes', 'Portal::addOcupante/$1');
    $routes->post('portal/ocupacion/(:num)/ocupantes/(:num)/invitar', 'Portal::invitarOcupante/$1/$2');
    $routes->post('portal/ocupacion/(:num)/ocupantes/(:num)/eliminar', 'Portal::removeOcupante/$1/$2');

    // Switch active condominio (tenant context) — any logged-in user, validated by service.
    $routes->post('condominio/activo', 'Condominios::setActivo');

    // Access supervision panel (condominio-wide, not persona-scoped).
    $routes->group('accesos', ['filter' => 'permission:accesos.supervisar'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Accesos::index');
        $routes->get('(:num)', 'Accesos::detail/$1');
    });

    // Caseta operations: scan + register entry/exit.
    $routes->group('caseta', ['filter' => 'permission:caseta.operate'], static function (RouteCollection $routes): void {
        $routes->get('escaner', 'Caseta::escaner');
        $routes->get('accesos/(:num)/checkin', 'Caseta::checkinForm/$1');
        $routes->post('accesos/(:num)/checkin', 'Caseta::checkin/$1');
        $routes->post('accesos/(:num)/checkout', 'Caseta::checkout/$1');
    });

    // Condominios management — platform level (superadmin via permission).
    $routes->group('condominios', ['filter' => 'permission:condominios.manage'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Condominios::index');
        $routes->get('nuevo', 'Condominios::new');
        $routes->post('/', 'Condominios::create');
        $routes->get('(:num)/editar', 'Condominios::edit/$1');
        $routes->post('(:num)', 'Condominios::update/$1');
        $routes->post('(:num)/eliminar', 'Condominios::delete/$1');
    });

    // Propiedades — scoped to the active condominio.
    $routes->group('torres', ['filter' => 'permission:propiedades.manage'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Torres::index');
        $routes->get('nueva', 'Torres::new');
        $routes->post('/', 'Torres::create');
        $routes->get('(:num)/editar', 'Torres::edit/$1');
        $routes->post('(:num)', 'Torres::update/$1');
        $routes->post('(:num)/eliminar', 'Torres::delete/$1');
    });

    $routes->group('casas', ['filter' => 'permission:propiedades.manage'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Casas::index');
        $routes->get('nueva', 'Casas::new');
        $routes->post('/', 'Casas::create');
        $routes->get('(:num)/editar', 'Casas::edit/$1');
        $routes->post('(:num)', 'Casas::update/$1');
        $routes->post('(:num)/eliminar', 'Casas::delete/$1');

        // Owners of a casa (casa_propietarios).
        $routes->get('(:num)/propietarios', 'Propietarios::index/$1');
        $routes->post('(:num)/propietarios', 'Propietarios::store/$1');
        $routes->post('(:num)/propietarios/(:num)/principal', 'Propietarios::principal/$1/$2');
        $routes->post('(:num)/propietarios/(:num)/eliminar', 'Propietarios::destroy/$1/$2');

        // Occupancy of a casa (ocupaciones + ocupantes).
        $routes->get('(:num)/ocupaciones', 'Ocupaciones::index/$1');
        $routes->get('(:num)/ocupaciones/nueva', 'Ocupaciones::new/$1');
        $routes->post('(:num)/ocupaciones', 'Ocupaciones::create/$1');
        $routes->get('(:num)/ocupaciones/(:num)/editar', 'Ocupaciones::edit/$1/$2');
        $routes->post('(:num)/ocupaciones/(:num)', 'Ocupaciones::update/$1/$2');
        $routes->post('(:num)/ocupaciones/(:num)/eliminar', 'Ocupaciones::delete/$1/$2');
        $routes->post('(:num)/ocupaciones/(:num)/ocupantes', 'Ocupaciones::addOcupante/$1/$2');
        $routes->post('(:num)/ocupaciones/(:num)/invitar-ocupante', 'Ocupaciones::invitarOcupante/$1/$2');
        $routes->post('(:num)/ocupaciones/(:num)/ocupantes/(:num)/principal', 'Ocupaciones::principalOcupante/$1/$2/$3');
        $routes->post('(:num)/ocupaciones/(:num)/ocupantes/(:num)/eliminar', 'Ocupaciones::removeOcupante/$1/$2/$3');
    });

    $routes->group('cajones', ['filter' => 'permission:propiedades.manage'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Cajones::index');
        $routes->get('nuevo', 'Cajones::new');
        $routes->post('/', 'Cajones::create');
        $routes->get('(:num)/editar', 'Cajones::edit/$1');
        $routes->post('(:num)', 'Cajones::update/$1');
        $routes->post('(:num)/eliminar', 'Cajones::delete/$1');
    });

    // Personas — scoped to the active condominio.
    $routes->group('personas', ['filter' => 'permission:personas.manage'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Personas::index');
        $routes->get('nueva', 'Personas::new');
        $routes->post('/', 'Personas::create');
        $routes->get('(:num)/editar', 'Personas::edit/$1');
        $routes->post('(:num)', 'Personas::update/$1');
        $routes->post('(:num)/eliminar', 'Personas::delete/$1');

        // Resident login account / invitation for a persona.
        $routes->get('(:num)/cuenta', 'Cuentas::index/$1');
        $routes->post('(:num)/cuenta', 'Cuentas::store/$1');
        $routes->post('(:num)/invitacion', 'Cuentas::invitar/$1');
    });
});
