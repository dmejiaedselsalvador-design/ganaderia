@props(['href' => null, 'variante' => 'primario', 'icono' => null])
@php
    $cls = match ($variante) {
        'primario'   => 'bg-emerald-600 text-white hover:bg-emerald-700',
        'oscuro'     => 'bg-slate-900 text-white hover:bg-slate-800',
        'peligro'    => 'bg-rose-600 text-white hover:bg-rose-700',
        default      => 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
    };
    $cls = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold shadow-sm transition ' . $cls;
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>@if($icono)<i class="fa-solid {{ $icono }}"></i>@endif {{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $cls, 'type' => 'submit']) }}>@if($icono)<i class="fa-solid {{ $icono }}"></i>@endif {{ $slot }}</button>
@endif
