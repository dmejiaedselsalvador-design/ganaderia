{{-- Sidebar de la planta GANADERÍA. Cada módulo se muestra solo si el usuario tiene su permiso. --}}
@php
    $base = 'group relative flex w-full items-center gap-3 rounded-xl px-3 py-2 text-xs sm:text-sm font-medium transition-all duration-200';
    $on   = 'bg-gradient-to-r from-emerald-600/30 to-transparent text-white font-semibold';
    $off  = 'text-emerald-100/60 hover:bg-white/5 hover:text-white';
    $bar  = 'absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-amber-300';
    $sub  = fn ($act) => 'flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-xs font-medium transition ' . ($act ? 'bg-emerald-600/30 text-white font-semibold' : 'text-emerald-100/50 hover:bg-white/5 hover:text-white');
    $titulo = 'mb-2 mt-4 px-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-emerald-200/30';

    $grupos = [
        'Engorda & Zootecnia' => [
            ['ganaderia.corrales',  'corrales.index',  'corrales.*',  'fa-warehouse',     'Corrales & Lotes'],
            ['ganaderia.nutricion', 'nutricion.index', 'nutricion.*', 'fa-wheat-awn',     'Nutrición & Raciones'],
            ['ganaderia.sanidad',   'sanidad.index',   'sanidad.*',   'fa-notes-medical', 'Sanidad & Retiro'],
        ],
        'Hato & Comercialización' => [
            ['ganaderia.ganado',  'compras.ganado.index',  'compras.ganado.index',  'fa-cow',            'Inventario de Ganado'],
            ['ganaderia.ganado',  'compras.ganado.perfil', 'compras.ganado.perfil', 'fa-id-card',        'Expediente Bovino'],
            ['ganaderia.calidad', 'calidad.carne.index',   'calidad.carne.*',       'fa-drumstick-bite', 'Calidad Carne & Canal'],
            ['ganaderia.ventas',  'ventas.index',          'ventas.*',              'fa-cart-shopping',  'Ventas de Ganado'],
            ['ganaderia.ganado',  'ganado.bajas.index',    'ganado.bajas.*',        'fa-skull',          'Bajas / Mortalidad'],
        ],
        'Finanzas & Proyecciones' => [
            ['ganaderia.simulador', 'simulador.index', 'simulador.index', 'fa-calculator', 'Simulador Engorda MXN'],
        ],
    ];
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="app-sidebar fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden text-emerald-100/70 transition-transform duration-300 ease-in-out md:static md:inset-auto md:translate-x-0">

    <!-- Decoración -->
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-500/15 blur-3xl"></div>
        <div class="absolute -bottom-28 -right-20 h-72 w-72 rounded-full bg-amber-400/10 blur-3xl"></div>
        <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-amber-300/30 to-transparent"></div>
    </div>

    <!-- Logo -->
    <a href="{{ route('welcome') }}" class="relative flex h-20 items-center gap-3 px-6">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-300/30 bg-amber-300/10 text-amber-300">
            <i class="fa-solid fa-cow text-lg"></i>
        </div>
        <div class="leading-tight">
            <span class="block font-['Playfair_Display'] text-lg font-semibold tracking-wide text-white">Ganadería<span class="text-amber-300">System</span></span>
            <span class="block text-[10px] uppercase tracking-[0.3em] text-emerald-200/40">{{ $planta['marca'] ?? 'Control integral' }}</span>
        </div>
    </a>
    <div class="relative mx-6 h-px bg-gradient-to-r from-amber-300/40 via-white/10 to-transparent"></div>

    <!-- Navegación -->
    <nav class="relative flex-1 space-y-1 overflow-y-auto px-4 py-4">

        <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-emerald-200/30">Principal</p>

        @php $act = request()->routeIs('welcome') || request()->routeIs('dashboard'); @endphp
        <a href="{{ route('welcome') }}" class="{{ $base }} {{ $act ? $on : $off }}">
            @if($act)<span class="{{ $bar }}"></span>@endif
            <i class="fa-solid fa-chart-pie w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> Dashboard de Análisis
        </a>

        @can('ganaderia.proveedores')
            <p class="{{ $titulo }}">Compra & Proveedores</p>
            <div x-data="{ open: {{ request()->routeIs('proveedores.*', 'provedores.*', 'proveedor.*', 'adelantos.*') ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" class="{{ $base }} {{ $off }} justify-between">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-truck-field w-5 text-center group-hover:text-amber-200"></i> Proveedores & Guías
                    </span>
                    <i class="fa-solid fa-chevron-down text-[10px] text-emerald-200/40 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-300' : ''"></i>
                </button>
                <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="ml-6 space-y-1 border-l border-white/10 pl-3 pt-1">
                    <a href="{{ route('proveedores.index') }}" class="{{ $sub(request()->routeIs('proveedores.index')) }}">
                        <i class="fa-solid fa-list-check w-4 text-center"></i> Vendedores
                    </a>
                    <a href="{{ route('proveedores.facturas.index') }}" class="{{ $sub(request()->routeIs('proveedores.facturas.*')) }}">
                        <i class="fa-solid fa-file-invoice w-4 text-center"></i> Guías de Compra
                    </a>
                    <a href="{{ route('proveedores.deudores.lista') }}" class="{{ $sub(request()->routeIs('proveedores.deudores.lista', 'provedores.deudores.lista')) }}">
                        <i class="fa-solid fa-money-bill-transfer w-4 text-center"></i> Deudores
                    </a>
                    <a href="{{ route('adelantos.index') }}" class="{{ $sub(request()->routeIs('adelantos.*')) }}">
                        <i class="fa-solid fa-hand-holding-dollar w-4 text-center"></i> Anticipos Proveedor
                    </a>
                </div>
            </div>
        @endcan

        @foreach ($grupos as $tituloGrupo => $items)
            @php $visibles = collect($items)->filter(fn ($i) => auth()->user()->can($i[0])); @endphp
            @if ($visibles->isNotEmpty())
                <p class="{{ $titulo }}">{{ $tituloGrupo }}</p>
                @foreach ($visibles as [$permiso, $ruta, $patron, $icono, $texto])
                    @php $act = request()->routeIs($patron); @endphp
                    <a href="{{ route($ruta) }}" class="{{ $base }} {{ $act ? $on : $off }}">
                        @if($act)<span class="{{ $bar }}"></span>@endif
                        <i class="fa-solid {{ $icono }} w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> {{ $texto }}
                    </a>
                @endforeach
            @endif
        @endforeach
    </nav>

    <!-- Pie -->
    <div class="relative m-4 rounded-xl border border-white/10 bg-white/5 p-4 text-center backdrop-blur">
        <p class="text-[10px] uppercase tracking-[0.25em] text-amber-200/70">{{ config('plantas.grupo') }}</p>
        <p class="mt-1 text-xs text-emerald-100/40">Planta <span class="font-semibold text-emerald-100/70">Ganadería</span> · v1.1</p>
    </div>
</aside>
