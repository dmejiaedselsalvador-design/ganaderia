@props(['titulo', 'subtitulo' => null, 'seccion' => 'Ganadería', 'icono' => null])
<div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
    <div class="flex items-start gap-3">
        @if ($icono)
            <div class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 sm:flex"><i class="fa-solid {{ $icono }}"></i></div>
        @endif
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">{{ $seccion }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">{{ $titulo }}</h1>
            @if ($subtitulo)<p class="mt-1 text-sm text-slate-500">{{ $subtitulo }}</p>@endif
        </div>
    </div>
    @isset($acciones)
        <div class="flex flex-wrap items-center gap-2">{{ $acciones }}</div>
    @endisset
</div>
