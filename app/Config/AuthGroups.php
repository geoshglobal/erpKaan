<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'inquilino';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Control total de la plataforma y todos los condominios.',
        ],
        'admin' => [
            'title'       => 'Administrador',
            'description' => 'Administra el día a día de uno o varios condominios.',
        ],
        'comite' => [
            'title'       => 'Comité / Mesa directiva',
            'description' => 'Supervisa finanzas, amenidades y comunicación del condominio.',
        ],
        'caseta' => [
            'title'       => 'Caseta / Seguridad',
            'description' => 'Opera el control de acceso: visitas, paquetería y bitácora.',
        ],
        'dueno' => [
            'title'       => 'Dueño',
            'description' => 'Propietario de una o varias casas.',
        ],
        'inquilino' => [
            'title'       => 'Inquilino',
            'description' => 'Habitante en renta de una casa.',
        ],
        'huesped' => [
            'title'       => 'Huésped',
            'description' => 'Acceso temporal (renta vacacional / visita prolongada).',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'admin.access'        => 'Acceder al back office de administración',
        'condominios.manage'  => 'Crear y administrar condominios (multi-tenant)',
        'propiedades.manage'  => 'Administrar torres, casas y cajones',
        'personas.manage'     => 'Administrar dueños e inquilinos',
        'ocupaciones.manage'  => 'Administrar ocupaciones y rentas',
        'accesos.manage'      => 'Crear/gestionar solicitudes de acceso y QR',
        'accesos.supervisar'  => 'Ver y supervisar todos los accesos del condominio',
        'caseta.operate'      => 'Operar caseta: cambiar status, registrar entradas/salidas',
        'finanzas.manage'     => 'Administrar cuotas, multas y pagos',
        'amenidades.manage'   => 'Configurar y reservar áreas comunes',
        'comunicacion.manage' => 'Enviar avisos y gestionar tickets',
        'self.access'         => 'Acceder al portal del residente (datos propios)',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            'admin.access',
            'condominios.manage',
            'propiedades.manage',
            'personas.manage',
            'ocupaciones.manage',
            'accesos.manage',
            'accesos.supervisar',
            'caseta.operate',
            'finanzas.manage',
            'amenidades.manage',
            'comunicacion.manage',
            'self.access',
        ],
        'admin' => [
            'admin.access',
            'propiedades.manage',
            'personas.manage',
            'ocupaciones.manage',
            'accesos.manage',
            'accesos.supervisar',
            'caseta.operate',
            'finanzas.manage',
            'amenidades.manage',
            'comunicacion.manage',
            'self.access',
        ],
        'comite' => [
            'admin.access',
            'accesos.supervisar',
            'finanzas.manage',
            'amenidades.manage',
            'comunicacion.manage',
            'self.access',
        ],
        'caseta' => [
            'accesos.manage',
            'accesos.supervisar',
            'caseta.operate',
        ],
        'dueno' => [
            'accesos.manage',
            'self.access',
        ],
        'inquilino' => [
            'accesos.manage',
            'self.access',
        ],
        'huesped' => [
            'self.access',
        ],
    ];
}
