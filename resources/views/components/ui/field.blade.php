@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false, 'ayuda' => null, 'options' => null, 'placeholder' => null, 'step' => null, 'colspan' => ''])
@php
    $id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name));
    $val = old(str_replace(['[', ']'], ['.', ''], $name), $value);
    $cls = 'mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm text-slate-800 shadow-xs focus:border-emerald-500 focus:ring-emerald-500';
@endphp
<div class="{{ $colspan }}">
    <label for="{{ $id }}" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">{{ $label }}@if($required) <span class="text-rose-500">*</span>@endif</label>
    @if ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" @required($required) {{ $attributes->except('id')->merge(['class' => $cls]) }}>
            @if ($placeholder !== false)<option value="">{{ $placeholder ?? '— Selecciona —' }}</option>@endif
            @foreach ($options ?? [] as $k => $t)
                <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $t }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="2" @required($required) placeholder="{{ $placeholder }}" {{ $attributes->except('id')->merge(['class' => $cls]) }}>{{ $val }}</textarea>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}" @required($required) placeholder="{{ $placeholder }}"
               @if($step) step="{{ $step }}" @endif {{ $attributes->except('id')->merge(['class' => $cls]) }}>
    @endif
    @if ($ayuda)<p class="mt-1 text-[11px] text-slate-400">{{ $ayuda }}</p>@endif
    @error(str_replace(['[', ']'], ['.', ''], $name))<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
