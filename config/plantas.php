<?php

/*
|--------------------------------------------------------------------------
| Plantas / Divisiones del Grupo
|--------------------------------------------------------------------------
| Cada planta es un emprendimiento independiente dentro del mismo sistema.
| - 'permiso'  : permiso de Spatie que da acceso a la planta (se asigna por usuario).
| - 'ruta'     : nombre de la ruta del dashboard de la planta.
| - 'prefijos' : prefijos de nombres de ruta que pertenecen a la planta.
|
| Para agregar una nueva planta: añadirla aquí, crear su archivo de rutas
| con el middleware 'division:<clave>' y ejecutar el RolesYPermisosSeeder.
*/

return [

    'grupo' => env('APP_GRUPO', 'Grupo Taurino'),

    // Clave de sesión donde se guarda la planta activa.
    'session_key' => 'active_division',

    'plantas' => [

        'ganaderia' => [
            'nombre'      => 'Ganadería',
            'marca'       => 'Rancho Taurino',
            'descripcion' => 'Hato, pesajes, engorda, sanidad, compras y ventas de ganado.',
            'icono'       => 'fa-cow',
            'acento'      => '#10b981', // esmeralda
            'permiso'     => 'planta.ganaderia',
            'ruta'        => 'welcome',
        ],

        'construccion' => [
            'nombre'      => 'Construcción',
            'marca'       => 'Constructora Taurino',
            'descripcion' => 'Proyectos de obra, presupuestos, materiales y personal.',
            'icono'       => 'fa-helmet-safety',
            'acento'      => '#f59e0b', // ámbar
            'permiso'     => 'planta.construccion',
            'ruta'        => 'construccion.dashboard',
        ],

    ],
];
