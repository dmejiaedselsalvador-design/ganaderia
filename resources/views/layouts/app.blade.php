<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sistema de Ganadería') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts (Laravel Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        <!-- Sidebar / Menú Lateral Profesional -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-auto border-r border-slate-800">

            <!-- Logo del Sistema -->
            <div class="flex items-center justify-center h-16 bg-slate-950 text-white font-bold text-lg gap-2.5 border-b border-slate-800/80">
                <div class="w-9 h-9 rounded-xl bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <i class="fa-solid fa-cow"></i>
                </div>
                <span class="tracking-wide">Ganadería<span class="text-emerald-400">System</span></span>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 px-3 py-5 space-y-1.5 overflow-y-auto">

                <!-- Enlace al Dashboard -->
                <a href="{{ route('welcome') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('welcome') || request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-900/50' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i> Dashboard
                </a>

                <!-- Menú Desplegable de Proveedores / Vendedores -->
                <div x-data="{ open: {{ request()->routeIs('proveedores.*') ? 'true' : 'false' }} }" class="space-y-1">
                    <!-- Botón Principal del Desplegable -->
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition hover:bg-slate-800/60 text-slate-400 hover:text-slate-200">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-truck-field w-5 text-center"></i>
                            <span>Proveedores</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 text-slate-500" :class="open ? 'rotate-180 text-slate-300' : ''"></i>
                    </button>

                    <!-- Submenú / Opciones desplegables -->
                    <div x-show="open" x-cloak class="pl-9 space-y-1 pt-1">
                        <!-- Opción: Vendedores -->
                        <a href="{{ route('proveedores.index') }}" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('proveedores.index') ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200' }}">
                            <i class="fa-solid fa-list-check w-4 text-center"></i> Vendedores
                        </a>

                        <!-- Opción: Guias -->
                        <a href="{{ route('proveedores.facturas.index') }}" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('proveedores.facturas.index') ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200' }}">
                            <i class="fa-solid fa-file-invoice w-4 text-center"></i> Guias
                        </a>

                        <!-- Opción: Deudores (¡Aquí estaba el error de etiqueta sin cerrar!) -->
                        <a href="{{ route('provedores.deudores.lista') }}" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition {{ request()->routeIs('provedores.deudores.lista') ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200' }}">
                            <i class="fa-solid fa-money-bill-transfer w-4 text-center"></i> Deudores
                        </a>
                    </div>
                </div>

                <!-- Enlace de Compras de Ganado -->
                <a href="{{ route('compras.ganado.index') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('compras.ganado.index') ? 'bg-emerald-600 text-white shadow-sm' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200' }}">
                    <i class="fa-solid fa-cart-shopping w-5 text-center"></i>Ganado
                </a>

                <!-- Enlace de Perfil -->
                <a href="{{ route('compras.ganado.perfil') }}" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('compras.ganado.perfil') ? 'bg-emerald-600 text-white shadow-sm' : 'hover:bg-slate-800/60 text-slate-400 hover:text-slate-200' }}">
                    <i class="fa-solid fa-weight-scale w-5 text-center"></i> Perfil
                </a>

                <!-- Enlace de Exportación -->
                <a href="#" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition hover:bg-slate-800/60 text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i> Exportación USA
                </a>
            </nav>

            <!-- Pie del Sidebar -->
            <div class="p-4 bg-slate-950/60 border-t border-slate-800/80 text-xs text-slate-500 text-center">
                Módulo México - EE.UU. <span class="font-semibold text-slate-400">v1.0</span>
            </div>
        </aside>

        <!-- Fondo oscuro para móviles -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 md:hidden backdrop-blur-xs"></div>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col overflow-y-auto">

            <!-- Barra Superior -->
            @include('layouts.navigation')

            <!-- Cabecera opcional -->
            @if (isset($header))
                <header class="bg-white shadow-xs border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-4 px-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Cuerpo Dinámico -->
            <main class="p-6 space-y-6 flex-1">
                {{ $slot }}
            </main>
        </div>

    </div>
</body>
</html>
