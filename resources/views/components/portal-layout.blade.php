@props(['titulo' => 'Selecciona tu planta'])
@php
    $usuario = auth()->user();
    $tema = $usuario?->temaPreferido() ?? 'claro';
    $temaClass = $tema === 'oscuro' ? 'tema-oscuro tema-dark' : 'tema-' . $tema;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo }} · {{ config('plantas.grupo') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|playfair-display:600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-['Inter'] antialiased {{ $temaClass }}" data-tema="{{ $tema }}">
    <div class="main-content-area min-h-screen">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-amber-300 shadow-sm">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="leading-tight">
                    <span class="block font-['Playfair_Display'] text-lg font-bold text-slate-900">{{ config('plantas.grupo') }}</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.25em] text-slate-500">Portal de plantas</span>
                </div>
            </div>
            @if ($usuario)
                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">{{ $usuario->name }}</p>
                        <p class="text-xs text-slate-500">{{ $usuario->roles->sortByDesc('nivel')->first()->display_name ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 shadow-sm hover:bg-rose-50">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            @endif
        </header>
        <main class="mx-auto max-w-6xl px-6 pb-16">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
