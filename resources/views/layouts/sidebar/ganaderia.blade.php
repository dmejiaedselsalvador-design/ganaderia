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
            [null, 'welcome', ['welcome', 'dashboard'], 'fa-gauge-high', 'Inicio'],
            ['ganaderia.ganado', 'campo.index', 'campo.*', 'fa-mobile-screen-button', 'Modo campo (celular)'],
        ],
        'Trabajo diario' => [
            ['grupo' => 'Compras y proveedores', 'icono' => 'fa-truck-field', 'items' => [
                ['ganaderia.proveedores', 'proveedores.index', ['proveedores.index', 'proveedor.*'], 'fa-list-check', 'Proveedores (ranchos)'],
                ['ganaderia.proveedores', 'proveedores.facturas.index', ['proveedores.facturas.*', 'compras.costos.*', 'compras.nuevo.ganado'], 'fa-file-invoice', 'Guías de compra'],
                ['ganaderia.proveedores', 'proveedores.deudores.lista', ['proveedores.deudores.lista', 'provedores.deudores.lista'], 'fa-money-bill-transfer', 'Saldos con proveedores'],
                ['ganaderia.proveedores', 'adelantos.index', 'adelantos.*', 'fa-hand-holding-dollar', 'Anticipos'],
            ]],
            ['grupo' => 'Ganado y corrales', 'icono' => 'fa-cow', 'items' => [
                ['ganaderia.ganado', 'compras.ganado.index', 'compras.ganado.index', 'fa-list', 'Ganado en existencia'],
                ['ganaderia.ganado', 'compras.ganado.perfil', ['compras.ganado.perfil', 'ganado.ficha'], 'fa-id-card', 'Buscar animal por arete'],
                ['ganaderia.corrales', 'corrales.index', 'corrales.*', 'fa-warehouse', 'Corrales y lotes'],
                ['ganaderia.ganado', 'ganado.bajas.index', 'ganado.bajas.*', 'fa-skull', 'Muertes y bajas'],
            ]],
            ['grupo' => 'Alimentación', 'icono' => 'fa-wheat-awn', 'items' => [
                ['ganaderia.nutricion', 'nutricion.index', ['nutricion.index', 'raciones.*'], 'fa-bowl-food', 'Raciones y comida servida'],
                ['ganaderia.nutricion', 'nutricion.planeacion', 'nutricion.plan*', 'fa-diagram-project', 'Plan de alimentación'],
                ['ganaderia.inventario', 'inventario.index', 'inventario.*', 'fa-boxes-stacked', 'Bodega (alimento y medicina)'],
            ]],
            ['grupo' => 'Salud del ganado', 'icono' => 'fa-notes-medical', 'items' => [
                ['ganaderia.sanidad', 'sanidad.index', 'sanidad.index', 'fa-syringe', 'Vacunas y medicinas'],
                ['ganaderia.sanidad', 'sanidad.calendario', 'sanidad.calendario', 'fa-calendar-check', 'Calendario de vacunas'],
                ['ganaderia.sanidad', 'sanidad.enfermedades', 'sanidad.enfermedades', 'fa-virus', 'Animales enfermos'],
            ]],
        ],
        'Ventas y dinero' => [
            ['grupo' => 'Ventas y cobranza', 'icono' => 'fa-cart-shopping', 'items' => [
                ['ganaderia.ventas', 'ventas.index', 'ventas.*', 'fa-cart-shopping', 'Ventas de ganado'],
                ['ganaderia.clientes', 'clientes.index', 'clientes.*', 'fa-address-book', 'Clientes'],
                ['ganaderia.clientes', 'cobranza.index', 'cobranza.*', 'fa-hand-holding-dollar', 'Cobranza (lo que nos deben)'],
                ['ganaderia.calidad', 'calidad.carne.index', 'calidad.carne.*', 'fa-drumstick-bite', 'Rendimiento en rastro'],
            ]],
            ['grupo' => 'Números del rancho', 'icono' => 'fa-chart-column', 'items' => [
                ['ganaderia.reportes', 'reportes.index', 'reportes.*', 'fa-chart-column', 'Ganancias y reportes'],
                ['ganaderia.gastos', 'gastos.index', 'gastos.*', 'fa-receipt', 'Gastos del rancho'],
                ['ganaderia.simulador', 'simulador.index', 'simulador.index', 'fa-calculator', 'Calculadora de engorda'],
                ['ganaderia.configuracion', 'configuracion.index', 'configuracion.*', 'fa-sliders', 'Precios, metas y ranchos'],
                ['registros.corregir', 'correcciones.index', 'correcciones.*', 'fa-pen-to-square', 'Corregir capturas'],
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
