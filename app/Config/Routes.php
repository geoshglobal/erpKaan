<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Shield auth routes (login, register, logout, magic-link, etc.)
service('auth')->routes($routes);

// Authenticated area
$routes->group('', ['filter' => 'session'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'Dashboard::index');

    // Switch active condominio (tenant context) — any logged-in user, validated by service.
    $routes->post('condominio/activo', 'Condominios::setActivo');

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
    });

    $routes->group('cajones', ['filter' => 'permission:propiedades.manage'], static function (RouteCollection $routes): void {
        $routes->get('/', 'Cajones::index');
        $routes->get('nuevo', 'Cajones::new');
        $routes->post('/', 'Cajones::create');
        $routes->get('(:num)/editar', 'Cajones::edit/$1');
        $routes->post('(:num)', 'Cajones::update/$1');
        $routes->post('(:num)/eliminar', 'Cajones::delete/$1');
    });
});
