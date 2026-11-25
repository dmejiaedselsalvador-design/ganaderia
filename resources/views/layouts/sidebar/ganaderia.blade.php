{{-- Sidebar de la planta GANADERÍA. Cada opción se muestra solo si el usuario tiene su permiso. --}}
@include('layouts.sidebar._menu', [
    'marca' => [
        'icono'     => 'fa-cow',
        'titulo'    => 'Ganadería<span class="text-amber-300">System</span>',
        'subtitulo' => $planta['marca'] ?? 'Control integral',
        'ruta'      => 'welcome',
    ],
    'secciones' => [
        '' => [
            [null, 'welcome', ['welcome', 'dashboard'], 'fa-gauge-high', 'Dashboard'],
            ['ganaderia.ganado', 'campo.index', 'campo.*', 'fa-mobile-screen-button', 'Modo campo'],
        ],
        'Operación' => [
            ['grupo' => 'Compras y proveedores', 'icono' => 'fa-truck-field', 'items' => [
                ['ganaderia.proveedores', 'proveedores.index', ['proveedores.index', 'proveedor.*'], 'fa-list-check', 'Vendedores'],
                ['ganaderia.proveedores', 'proveedores.facturas.index', ['proveedores.facturas.*', 'compras.costos.*', 'compras.nuevo.ganado'], 'fa-file-invoice', 'Guías de compra'],
                ['ganaderia.proveedores', 'proveedores.deudores.lista', ['proveedores.deudores.lista', 'provedores.deudores.lista'], 'fa-money-bill-transfer', 'Deudores'],
                ['ganaderia.proveedores', 'adelantos.index', 'adelantos.*', 'fa-hand-holding-dollar', 'Anticipos'],
            ]],
            ['grupo' => 'Hato y corrales', 'icono' => 'fa-cow', 'items' => [
                ['ganaderia.ganado', 'compras.ganado.index', 'compras.ganado.index', 'fa-list', 'Inventario de ganado'],
                ['ganaderia.ganado', 'compras.ganado.perfil', ['compras.ganado.perfil', 'ganado.ficha'], 'fa-id-card', 'Expediente bovino'],
                ['ganaderia.corrales', 'corrales.index', 'corrales.*', 'fa-warehouse', 'Corrales y lotes'],
                ['ganaderia.ganado', 'ganado.bajas.index', 'ganado.bajas.*', 'fa-skull', 'Bajas'],
            ]],
            ['grupo' => 'Alimentación', 'icono' => 'fa-wheat-awn', 'items' => [
                ['ganaderia.nutricion', 'nutricion.index', ['nutricion.index', 'raciones.*'], 'fa-bowl-food', 'Dietas y servidos'],
                ['ganaderia.nutricion', 'nutricion.planeacion', 'nutricion.plan*', 'fa-diagram-project', 'Plan de alimentación'],
                ['ganaderia.inventario', 'inventario.index', 'inventario.*', 'fa-boxes-stacked', 'Inventario de insumos'],
            ]],
            ['grupo' => 'Sanidad', 'icono' => 'fa-notes-medical', 'items' => [
                ['ganaderia.sanidad', 'sanidad.index', 'sanidad.index', 'fa-syringe', 'Tratamientos y retiro'],
                ['ganaderia.sanidad', 'sanidad.calendario', 'sanidad.calendario', 'fa-calendar-check', 'Calendario sanitario'],
                ['ganaderia.sanidad', 'sanidad.enfermedades', 'sanidad.enfermedades', 'fa-virus', 'Enfermedades'],
            ]],
        ],
        'Comercial y finanzas' => [
            ['grupo' => 'Ventas y cobranza', 'icono' => 'fa-cart-shopping', 'items' => [
                ['ganaderia.ventas', 'ventas.index', 'ventas.*', 'fa-cart-shopping', 'Ventas de ganado'],
                ['ganaderia.clientes', 'clientes.index', 'clientes.*', 'fa-address-book', 'Clientes'],
                ['ganaderia.clientes', 'cobranza.index', 'cobranza.*', 'fa-hand-holding-dollar', 'Cuentas por cobrar'],
                ['ganaderia.calidad', 'calidad.carne.index', 'calidad.carne.*', 'fa-drumstick-bite', 'Calidad de canal'],
            ]],
            ['grupo' => 'Finanzas', 'icono' => 'fa-chart-column', 'items' => [
                ['ganaderia.reportes', 'reportes.index', 'reportes.*', 'fa-chart-column', 'Rentabilidad y reportes'],
                ['ganaderia.gastos', 'gastos.index', 'gastos.*', 'fa-receipt', 'Gastos operativos'],
                ['ganaderia.simulador', 'simulador.index', 'simulador.index', 'fa-calculator', 'Simulador de engorda'],
                ['ganaderia.configuracion', 'configuracion.index', 'configuracion.*', 'fa-sliders', 'Precios, metas y ranchos'],
                ['registros.corregir', 'correcciones.index', 'correcciones.*', 'fa-pen-to-square', 'Corrección de registros'],
            ]],
        ],
    ],
    'tema' => [
        'texto'  => 'text-emerald-100/70',
        'activo' => 'from-emerald-600/30',
        'acento' => 'text-amber-300',
        'barra'  => 'bg-amber-300',
        'titulo' => 'text-emerald-200/40',
        'borde'  => 'via-amber-300/30',
        'activoBg' => 'bg-emerald-500/15',
    ],
    'pie' => config('plantas.grupo') . ' · Ganadería',
])
