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

        <!-- Sidebar / Menú Lateral (Estilo GanaderíaSystem) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:inset-auto">
            <div class="flex items-center justify-center h-16 bg-slate-950 text-white font-bold text-lg gap-2">
                <i class="fa-solid fa-cow text-emerald-400"></i>
                <span>GanaderíaSystem</span>
            </div>

           <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
    <!-- Enlace al Dashboard -->
    <a href="{{ route('dashboard') }}" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-medium' : 'hover:bg-slate-800 text-slate-300' }} transition">
        <i class="fa-solid fa-chart-pie"></i> Dashboard
    </a>

    <!-- Menú Desplegable de Proveedores / Vendedores -->
    <div x-data="{ open: {{ request()->routeIs('proveedores.*') ? 'true' : 'false' }} }" class="space-y-1">
        <!-- Botón Principal del Desplegable -->
        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-truck-field"></i>
                <span>Proveedores</span>
            </div>
            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
        </button>

        <!-- Submenú / Opciones desplegables -->
        <div x-show="open" x-cloak class="pl-9 space-y-1">
            <!-- Opción: Vendedores -->
            <a href="{{ route('proveedores.index') }}" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('proveedores.index') ? 'bg-emerald-600 text-white font-medium' : 'hover:bg-slate-800 text-slate-400' }} text-sm transition">
                <i class="fa-solid fa-list-check text-xs"></i> Vendedores
            </a>

            <!-- Opción: Facturas (Ejemplo de ruta) -->
            <a href="{{ route('proveedores.facturas.index') }}" class="w-full flex items-center gap-3 px-4 py-2 rounded-lg {{ request()->routeIs('proveedores.facturas.index') ? 'bg-emerald-600 text-white font-medium' : 'hover:bg-slate-800 text-slate-400' }} text-sm transition">
                <i class="fa-solid fa-file-invoice text-xs"></i> Facturas
            </a>
        </div>
    </div>

    <!-- Enlace de Compras de Ganado -->
    <a href="{{ route('compras.ganado.index') }}" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
        <i class="fa-solid fa-cart-shopping"></i> Compras de Ganado
    </a>

    <!-- Enlace de Perfil -->
    <a href="{{ route('compras.ganado.perfil') }}" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
        <i class="fa-solid fa-weight-scale"></i> Perfil
    </a>

    <!-- Enlace de Exportación -->
    <a href="#" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
        <i class="fa-solid fa-file-invoice-dollar"></i> Exportación USA
    </a>
</nav>

            <div class="p-4 bg-slate-950 text-xs text-slate-500 text-center">
                Módulo México - EE.UU. v1.0
            </div>
        </aside>

        <!-- Fondo oscuro para dispositivos móviles cuando el menú está abierto -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col overflow-y-auto">

            <!-- Barra Superior (Incluyendo navegación y datos del usuario de Laravel) -->
            @include('layouts.navigation')

            <!-- Page Heading (Cabecera opcional de vistas Laravel) -->
            @if (isset($header))
                <header class="bg-white shadow-xs border-b border-slate-200">
                    <div class="max-w-7xl mx-auto py-4 px-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Cuerpo Dinámico de la Página -->
            <main class="p-6 space-y-6 flex-1">
                {{ $slot }}
            </main>
        </div>

    </div>
</body>
</html>
