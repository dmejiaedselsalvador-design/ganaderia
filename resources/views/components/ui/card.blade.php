@props(['titulo' => null, 'subtitulo' => null, 'padding' => true])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-100 bg-white shadow-sm']) }}>
    @if ($titulo)
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">{{ $titulo }}</h3>
                @if ($subtitulo)<p class="text-xs text-slate-400">{{ $subtitulo }}</p>@endif
            </div>
            @isset($acciones){{ $acciones }}@endisset
        </div>
    @endif
    <div class="{{ $padding ? 'p-5' : '' }}">{{ $slot }}</div>
</div>
