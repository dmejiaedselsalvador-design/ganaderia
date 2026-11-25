{{-- Sidebar de la planta CONSTRUCCIÓN --}}
@php
    $base = 'group relative flex w-full items-center gap-3 rounded-xl px-3 py-2 text-xs sm:text-sm font-medium transition-all duration-200';
    $on   = 'bg-gradient-to-r from-amber-500/25 to-transparent text-white font-semibold';
    $off  = 'text-stone-300/70 hover:bg-white/5 hover:text-white';
    $bar  = 'absolute left-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-r-full bg-amber-400';
    $modulos = \App\Http\Controllers\Construccion\ConstruccionDashboardController::MODULOS;
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="app-sidebar fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden text-stone-300/80 transition-transform duration-300 ease-in-out md:static md:inset-auto md:translate-x-0">

    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 -left-24 h-72 w-72 rounded-full bg-amber-500/15 blur-3xl"></div>
        <div class="absolute -bottom-28 -right-20 h-72 w-72 rounded-full bg-orange-500/10 blur-3xl"></div>
        <div class="absolute inset-0 opacity-[0.05]" style="background-image: repeating-linear-gradient(135deg, #fbbf24 0 2px, transparent 2px 14px);"></div>
        <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-amber-400/30 to-transparent"></div>
    </div>

    <a href="{{ route('construccion.dashboard') }}" class="relative flex h-20 items-center gap-3 px-6">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-400/40 bg-amber-400/10 text-amber-400">
            <i class="fa-solid fa-helmet-safety text-lg"></i>
        </div>
        <div class="leading-tight">
            <span class="block font-['Playfair_Display'] text-lg font-semibold tracking-wide text-white">Construcción<span class="text-amber-400">.</span></span>
            <span class="block text-[10px] uppercase tracking-[0.3em] text-stone-400/60">{{ $planta['marca'] ?? 'Obras y proyectos' }}</span>
        </div>
    </a>
    <div class="relative mx-6 h-px bg-gradient-to-r from-amber-400/40 via-white/10 to-transparent"></div>

    <nav class="relative flex-1 space-y-1 overflow-y-auto px-4 py-4">
        <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-stone-400/40">Principal</p>

        @php $act = request()->routeIs('construccion.dashboard'); @endphp
        <a href="{{ route('construccion.dashboard') }}" class="{{ $base }} {{ $act ? $on : $off }}">
            @if($act)<span class="{{ $bar }}"></span>@endif
            <i class="fa-solid fa-chart-line w-5 text-center {{ $act ? 'text-amber-400' : 'group-hover:text-amber-300' }}"></i> Dashboard de Obras
        </a>

        <p class="mb-2 mt-4 px-3 text-[10px] font-semibold uppercase tracking-[0.25em] text-stone-400/40">Gestión</p>
        @foreach ($modulos as $clave => [$titulo, $icono])
            @can("construccion.$clave")
                @php $act = request()->routeIs('construccion.modulo') && request()->route('modulo') === $clave; @endphp
                <a href="{{ route('construccion.modulo', $clave) }}" class="{{ $base }} {{ $act ? $on : $off }}">
                    @if($act)<span class="{{ $bar }}"></span>@endif
                    <i class="fa-solid {{ $icono }} w-5 text-center {{ $act ? 'text-amber-400' : 'group-hover:text-amber-300' }}"></i>
                    <span class="flex-1">{{ $titulo }}</span>
                    <span class="rounded-full border border-amber-400/30 px-1.5 py-0.5 text-[9px] uppercase tracking-wider text-amber-300/80">Pronto</span>
                </a>
            @endcan
        @endforeach
    </nav>

    <div class="relative m-4 rounded-xl border border-white/10 bg-white/5 p-4 text-center backdrop-blur">
        <p class="text-[10px] uppercase tracking-[0.25em] text-amber-300/70">{{ config('plantas.grupo') }}</p>
        <p class="mt-1 text-xs text-stone-400/60">Planta <span class="font-semibold text-stone-300/80">Construcción</span> · v1.1</p>
    </div>
</aside>
