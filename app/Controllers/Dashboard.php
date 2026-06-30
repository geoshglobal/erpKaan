<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $user = auth()->user();

        // Module cards, each gated by a Shield permission. Until each module is
        // built they point to '#'; the role-based visibility is what we validate here.
        $modules = [
            ['perm' => 'condominios.manage',  'title' => 'Condominios',   'desc' => 'Alta y configuración de condominios',        'url' => site_url('condominios')],
            ['perm' => 'propiedades.manage',  'title' => 'Propiedades',    'desc' => 'Torres, casas y cajones',                    'url' => '#'],
            ['perm' => 'personas.manage',     'title' => 'Personas',       'desc' => 'Dueños e inquilinos',                        'url' => '#'],
            ['perm' => 'ocupaciones.manage',  'title' => 'Ocupaciones',    'desc' => 'Uso propio / renta lineal / vacacional',     'url' => '#'],
            ['perm' => 'accesos.manage',      'title' => 'Accesos',        'desc' => 'Visitas, QR, paquetería y delivery',         'url' => '#'],
            ['perm' => 'caseta.operate',      'title' => 'Caseta',         'desc' => 'Operación de control de acceso',             'url' => '#'],
            ['perm' => 'finanzas.manage',     'title' => 'Finanzas',       'desc' => 'Cuotas, multas y pagos',                     'url' => '#'],
            ['perm' => 'amenidades.manage',   'title' => 'Amenidades',     'desc' => 'Reserva de áreas comunes',                   'url' => '#'],
            ['perm' => 'comunicacion.manage', 'title' => 'Comunicación',   'desc' => 'Avisos y tickets',                           'url' => '#'],
            ['perm' => 'self.access',         'title' => 'Mi portal',      'desc' => 'Mis casas, visitas y estados de cuenta',     'url' => '#'],
        ];

        $allowed = array_values(array_filter(
            $modules,
            static fn (array $m): bool => $user->can($m['perm'])
        ));

        return view('dashboard/index', [
            'user'    => $user,
            'groups'  => $user->getGroups(),
            'modules' => $allowed,
        ]);
    }
}
