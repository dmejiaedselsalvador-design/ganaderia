{{-- Sidebar de la planta CONSTRUCCIÓN --}}
@php
    $mods = collect(\App\Http\Controllers\Construccion\ConstruccionDashboardController::MODULOS)
        ->map(fn ($m, $clave) => ["construccion.$clave", 'construccion.modulo', 'construccion.modulo', $m[1], $m[0], $clave]);
@endphp
@include('layouts.sidebar._menu', [
    'marca' => [
        'icono'     => 'fa-helmet-safety',
        'titulo'    => 'Construcción<span class="text-amber-400">.</span>',
        'subtitulo' => $planta['marca'] ?? 'Obras y proyectos',
        'ruta'      => 'construccion.dashboard',
    ],
    'secciones' => [
        '' => [
            [null, 'construccion.dashboard', 'construccion.dashboard', 'fa-gauge-high', 'Dashboard de obras'],
        ],
        'Gestión' => $mods->values()->all(),
    ],
    'tema' => [
        'texto'  => 'text-stone-300/80',
        'activo' => 'from-amber-500/25',
        'acento' => 'text-amber-400',
        'barra'  => 'bg-amber-400',
        'titulo' => 'text-stone-400/50',
        'borde'  => 'via-amber-400/30',
        'marca'  => 'border-amber-400/40 bg-amber-400/10 text-amber-400',
        'blur1'  => 'bg-amber-500/15',
        'blur2'  => 'bg-orange-500/10',
        'activoBg' => 'bg-amber-500/15',
    ],
    'pie' => config('plantas.grupo') . ' · Construcción',
])
