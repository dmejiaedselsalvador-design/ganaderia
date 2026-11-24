<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Ganadería') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700&display=swap" rel="stylesheet" />

    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts (Laravel Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a29e; }
        aside nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); }
    </style>
</head>
<body class="bg-stone-100 font-['Inter'] antialiased text-stone-800">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        <!-- ================= SIDEBAR ================= -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden bg-[#05201a] text-emerald-100/70 transition-transform duration-300 ease-in-out md:static md:inset-auto md:translate-x-0">

            <!-- Decoración -->
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-emerald-500/15 blur-3xl"></div>
                <div class="absolute -bottom-28 -right-20 h-72 w-72 rounded-full bg-amber-400/10 blur-3xl"></div>
                <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-amber-300/30 to-transparent"></div>
            </div>

            <!-- Logo -->
            <div class="relative flex h-20 items-center gap-3 px-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-300/30 bg-amber-300/10 text-amber-300">
                    <i class="fa-solid fa-cow text-lg"></i>
                </div>
                <div class="leading-tight">
                    <span class="block font-['Playfair_Display'] text-lg font-semibold tracking-wide text-white">Ganadería<span class="text-amber-300">System</span></span>
                    <span class="block text-[10px] uppercase tracking-[0.3em] text-emerald-200/40">Control integral</span>
                </div>
            </div>
            <div class="relative mx-6 h-px bg-gradient-to-r from-amber-300/40 via-white/10 to-transparent"></div>

            <!-- Navegación -->
            <nav class="relative flex-1 space-y-1 overflow-y-auto px-4 py-6">

                <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-emerald-200/30">Principal</p>

                @php
                    $base = 'group relative flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all duration-200';
                    $on   = 'bg-gradient-to-r from-emerald-600/30 to-transparent text-white';
                    $off  = 'text-emerald-100/60 hover:bg-white/5 hover:text-white';
                    $bar  = 'absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-amber-300';
                @endphp

                <!-- Dashboard -->
                @php $act = request()->routeIs('welcome') || request()->routeIs('dashboard'); @endphp
                <a href="{{ route('welcome') }}" class="{{ $base }} {{ $act ? $on : $off }}">
                    @if($act)<span class="{{ $bar }}"></span>@endif
                    <i class="fa-solid fa-chart-pie w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> Dashboard
                </a>

                <!-- Proveedores -->
                <div x-data="{ open: {{ request()->routeIs('proveedores.*') || request()->routeIs('provedores.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="{{ $base }} {{ $off }} justify-between">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-truck-field w-5 text-center group-hover:text-amber-200"></i> Proveedores
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-emerald-200/40 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-300' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="ml-6 space-y-1 border-l border-white/10 pl-3 pt-1">
                        @php $a1 = request()->routeIs('proveedores.index'); @endphp
                        <a href="{{ route('proveedores.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium transition {{ $a1 ? 'bg-emerald-600/30 text-white' : 'text-emerald-100/50 hover:bg-white/5 hover:text-white' }}">
                            <i class="fa-solid fa-list-check w-4 text-center {{ $a1 ? 'text-amber-300' : '' }}"></i> Vendedores
                        </a>
                        @php $a2 = request()->routeIs('proveedores.facturas.index'); @endphp
                        <a href="{{ route('proveedores.facturas.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium transition {{ $a2 ? 'bg-emerald-600/30 text-white' : 'text-emerald-100/50 hover:bg-white/5 hover:text-white' }}">
                            <i class="fa-solid fa-file-invoice w-4 text-center {{ $a2 ? 'text-amber-300' : '' }}"></i> Guias
                        </a>
                        @php $a3 = request()->routeIs('proveedores.deudores.lista') || request()->routeIs('provedores.deudores.lista'); @endphp
                        <a href="{{ route('proveedores.deudores.lista') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium transition {{ $a3 ? 'bg-emerald-600/30 text-white' : 'text-emerald-100/50 hover:bg-white/5 hover:text-white' }}">
                            <i class="fa-solid fa-money-bill-transfer w-4 text-center {{ $a3 ? 'text-amber-300' : '' }}"></i> Deudores
                        </a>
                    </div>
                </div>

                <p class="mb-3 mt-6 px-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-emerald-200/30">Operación</p>

                <!-- Ganado -->
                @php $act = request()->routeIs('compras.ganado.index'); @endphp
                <a href="{{ route('compras.ganado.index') }}" class="{{ $base }} {{ $act ? $on : $off }}">
                    @if($act)<span class="{{ $bar }}"></span>@endif
                    <i class="fa-solid fa-cow w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> Inventario Ganado
                </a>

                <!-- Perfil / Expediente -->
                @php $act = request()->routeIs('compras.ganado.perfil'); @endphp
                <a href="{{ route('compras.ganado.perfil') }}" class="{{ $base }} {{ $act ? $on : $off }}">
                    @if($act)<span class="{{ $bar }}"></span>@endif
                    <i class="fa-solid fa-id-card w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> Expediente Bovino
                </a>

                <!-- Ventas de Ganado -->
                @php $act = request()->routeIs('ventas.*'); @endphp
                <a href="{{ route('ventas.index') }}" class="{{ $base }} {{ $act ? $on : $off }}">
                    @if($act)<span class="{{ $bar }}"></span>@endif
                    <i class="fa-solid fa-cart-shopping w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> Ventas de Ganado
                </a>

                <!-- Bajas y Mortalidad -->
                @php $act = request()->routeIs('ganado.bajas.*'); @endphp
                <a href="{{ route('ganado.bajas.index') }}" class="{{ $base }} {{ $act ? $on : $off }}">
                    @if($act)<span class="{{ $bar }}"></span>@endif
                    <i class="fa-solid fa-skull w-5 text-center {{ $act ? 'text-amber-300' : 'group-hover:text-amber-200' }}"></i> Bajas / Mortalidad
                </a>
            </nav>

            <!-- Pie -->
            <div class="relative m-4 rounded-xl border border-white/10 bg-white/5 p-4 text-center backdrop-blur">
                <p class="text-[10px] uppercase tracking-[0.25em] text-amber-200/70">Módulo México · EE.UU.</p>
                <p class="mt-1 text-xs text-emerald-100/40">Versión <span class="font-semibold text-emerald-100/70">1.0</span></p>
            </div>
        </aside>

        <!-- Fondo oscuro móvil -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"></div>

        <!-- ================= CONTENIDO ================= -->
        <div class="flex flex-1 flex-col overflow-y-auto bg-gradient-to-b from-stone-100 to-stone-50">

            <!-- Barra superior -->
            @include('layouts.navigation')

            <!-- Cabecera opcional -->
            @if (isset($header))
                <header class="border-b border-stone-200 bg-white/80 backdrop-blur">
                    <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Cuerpo dinámico -->
            <main class="flex-1 space-y-6 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
