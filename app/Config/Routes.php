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
});
