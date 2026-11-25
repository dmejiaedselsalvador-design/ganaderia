@php
    use App\Support\Plantas;

    $usuario     = Auth::user();
    $tema        = $usuario?->temaPreferido() ?? 'claro';
    $temaClass   = $tema === 'oscuro' ? 'tema-oscuro tema-dark' : 'tema-' . $tema;
    $plantaClave = Plantas::activa()
        ?? (request()->routeIs('construccion.*') ? 'construccion' : 'ganaderia');
    $planta      = Plantas::info($plantaClave);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $planta['nombre'] ?? '' }} · {{ config('plantas.grupo') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|playfair-display:500,600,700&display=swap" rel="stylesheet" />

    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts (Laravel Vite) — incluye los temas en resources/css/temas.css -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 9999px; }
        aside nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); }
    </style>
</head>
<body class="font-['Inter'] antialiased {{ $temaClass }}" data-tema="{{ $tema }}" data-planta="{{ $plantaClave }}">

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

        <!-- ================= SIDEBAR (según planta activa) ================= -->
        @include('layouts.sidebar.' . $plantaClave, ['planta' => $planta])

        <!-- Fondo oscuro móvil -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"></div>

        <!-- ================= CONTENIDO ================= -->
        <div class="main-content-area flex flex-1 flex-col overflow-y-auto">

            <!-- Barra superior -->
            @include('layouts.navigation', ['planta' => $planta, 'plantaClave' => $plantaClave, 'tema' => $tema])

            <!-- Cabecera opcional -->
            @if (isset($header))
                <header class="border-b border-stone-200 bg-white/80 backdrop-blur">
                    <div class="mx-auto max-w-7xl px-6 py-4 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Mensajes del sistema -->
            @if (session('exito') || session('aviso'))
                <div class="px-6 pt-5" x-data="{ open: true }" x-show="open" x-transition.opacity>
                    <div class="mx-auto max-w-7xl flex items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-sm
                        {{ session('exito') ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                        <i class="fa-solid {{ session('exito') ? 'fa-circle-check' : 'fa-triangle-exclamation' }} mt-0.5"></i>
                        <span class="flex-1">{{ session('exito') ?? session('aviso') }}</span>
                        <button type="button" @click="open = false" class="opacity-60 hover:opacity-100" aria-label="Cerrar">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Cuerpo dinámico -->
            <main class="flex-1 space-y-6 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
