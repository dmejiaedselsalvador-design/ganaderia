<?php

namespace App\Support;

/**
 * Catálogo central de permisos y roles base del sistema.
 * Lo usa el RolesYPermisosSeeder y la pantalla de Roles.
 */
class Permisos
{
    /** Permisos agrupados por sección: nombre => etiqueta */
    public const GRUPOS = [
        'Acceso a plantas' => [
            'planta.ganaderia'    => 'Entrar a la planta Ganadería',
            'planta.construccion' => 'Entrar a la planta Construcción',
        ],
        'Ganadería' => [
            'ganaderia.finanzas'    => 'Ver montos y análisis financiero',
            'ganaderia.ganado'      => 'Inventario, expedientes, pesajes y bajas',
            'ganaderia.proveedores' => 'Proveedores, guías de compra, deudores y anticipos',
            'ganaderia.corrales'    => 'Corrales y lotes de engorda',
            'ganaderia.nutricion'   => 'Nutrición, raciones y consumos',
            'ganaderia.sanidad'     => 'Sanidad y control de retiro',
            'ganaderia.calidad'     => 'Calidad de carne y canal',
            'ganaderia.ventas'      => 'Ventas de ganado',
            'ganaderia.simulador'   => 'Simulador financiero de engorda',
            'ganaderia.gastos'      => 'Gastos operativos',
            'ganaderia.clientes'    => 'Clientes y cobranza',
            'ganaderia.inventario'  => 'Inventario de alimento y medicamentos',
            'ganaderia.reportes'    => 'Rentabilidad y reportes exportables',
            'ganaderia.configuracion' => 'Precios de mercado, ubicaciones y metas por lote',
            'registros.corregir'    => 'Corregir y anular registros capturados (pesajes, servidos, tratamientos, bajas, ventas, compra)',
        ],
        'Construcción' => [
            'construccion.finanzas'     => 'Ver montos y análisis financiero',
            'construccion.proyectos'    => 'Proyectos y obras',
            'construccion.presupuestos' => 'Presupuestos y cotizaciones',
            'construccion.materiales'   => 'Materiales e inventario',
            'construccion.personal'     => 'Personal y cuadrillas',
        ],
        'Administración' => [
            'usuarios.ver'      => 'Ver usuarios',
            'usuarios.crear'    => 'Crear usuarios',
            'usuarios.editar'   => 'Editar usuarios y asignar roles',
            'usuarios.baja'     => 'Dar de baja / reactivar usuarios',
            'usuarios.eliminar' => 'Eliminar usuarios definitivamente',
            'usuarios.solicitudes_clave' => 'Atender solicitudes de cambio de clave',
            'roles.gestionar'   => 'Crear y editar roles y permisos',
            'auditoria.ver'     => 'Ver la bitácora de auditoría',
            'ayuda.responder'   => 'Responder las preguntas enviadas desde el botón de Ayuda',
        ],
    ];

    /**
     * Roles base: nombre => [display_name, description, nivel, permisos].
     * super-admin y dueno obtienen todo mediante Gate::before.
     * El acceso a plantas (planta.*) se asigna por usuario, no por rol.
     */
    public const ROLES = [
        'super-admin' => [
            'Súper Administrador',
            'Acceso total al sistema. Nadie más puede modificarlo ni eliminarlo.',
            100,
            '*',
        ],
        'dueno' => [
            'Dueño',
            'Puede hacer cualquier cosa en todas las plantas, excepto eliminar o modificar al Súper Administrador.',
            90,
            '*',
        ],
        'gerente' => [
            'Gerente',
            'Gestión completa de las plantas asignadas, incluyendo finanzas y personal.',
            70,
            [
                'ganaderia.finanzas', 'ganaderia.ganado', 'ganaderia.proveedores', 'ganaderia.corrales',
                'ganaderia.nutricion', 'ganaderia.sanidad', 'ganaderia.calidad', 'ganaderia.ventas', 'ganaderia.simulador',
                'ganaderia.gastos', 'ganaderia.clientes', 'ganaderia.inventario', 'ganaderia.reportes', 'ganaderia.configuracion',
                'auditoria.ver', 'ayuda.responder', 'registros.corregir',
                'construccion.finanzas', 'construccion.proyectos', 'construccion.presupuestos',
                'construccion.materiales', 'construccion.personal',
                'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.baja',
            ],
        ],
        'supervisor' => [
            'Supervisor',
            'Supervisa la operación diaria: ganado, corrales, nutrición, sanidad, calidad y obras.',
            50,
            [
                'ganaderia.ganado', 'ganaderia.corrales', 'ganaderia.nutricion', 'ganaderia.sanidad',
                'ganaderia.calidad', 'ganaderia.ventas', 'ganaderia.proveedores', 'ganaderia.inventario', 'ganaderia.reportes',
                'construccion.proyectos', 'construccion.materiales', 'construccion.personal',
                'usuarios.ver',
            ],
        ],
        'operario' => [
            'Operario',
            'Registro operativo: pesajes, corrales, alimentación y tratamientos.',
            20,
            [
                'ganaderia.ganado', 'ganaderia.corrales', 'ganaderia.nutricion', 'ganaderia.sanidad',
                'construccion.proyectos', 'construccion.materiales',
            ],
        ],
        'vendedor' => [
            'Vendedor',
            'Comercialización: ventas de ganado, calidad de canal y presupuestos.',
            20,
            [
                'ganaderia.ventas', 'ganaderia.ganado', 'ganaderia.calidad', 'ganaderia.clientes',
                'construccion.presupuestos',
            ],
        ],
        'comprador' => [
            'Comprador',
            'Compras: proveedores, guías de compra, anticipos y materiales.',
            20,
            [
                'ganaderia.proveedores', 'ganaderia.ganado', 'ganaderia.inventario', 'ganaderia.gastos',
                'construccion.materiales',
            ],
        ],
    ];

    /** Roles que no se pueden eliminar ni renombrar. */
    public const ROLES_SISTEMA = ['super-admin', 'dueno'];

    public static function todos(): array
    {
        return array_merge(...array_values(self::GRUPOS));
    }

    public static function etiqueta(string $permiso): string
    {
        return self::todos()[$permiso] ?? $permiso;
    }
}
