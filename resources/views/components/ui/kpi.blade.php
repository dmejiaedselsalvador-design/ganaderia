@props(['label', 'valor', 'nota' => null, 'icono' => 'fa-chart-simple', 'color' => 'bg-emerald-50 text-emerald-600', 'valorClass' => 'text-slate-900'])
<div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
    <div class="flex items-center justify-between gap-2">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $color }}"><i class="fa-solid {{ $icono }} text-sm"></i></span>
    </div>
    <p class="mt-1 text-xl font-black {{ $valorClass }}">{{ $valor }}</p>
    @if ($nota)<p class="mt-0.5 truncate text-[11px] text-slate-400" title="{{ $nota }}">{{ $nota }}</p>@endif
</div>
