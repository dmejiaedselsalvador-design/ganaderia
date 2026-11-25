{{--
    Botón «Corregir» con formulario desplegable y motivo obligatorio.
    Uso: <x-ui.corregir :action="route(...)" anular="Anular pesaje"> campos… </x-ui.corregir>
         <x-ui.corregir :action="route(...)" solo="Revertir baja" />   (solo acción, sin campos)
--}}
@props(['action', 'anular' => null, 'solo' => null, 'texto' => 'Corregir', 'aviso' => null])
<div x-data="{ abierto: false }" {{ $attributes->merge(['class' => 'w-full']) }}>
    <div class="flex justify-end">
        <button type="button" @click="abierto = !abierto"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800">
            <i class="fa-solid" :class="abierto ? 'fa-xmark' : 'fa-pen-to-square'"></i> <span x-text="abierto ? 'Cerrar' : @js($solo ?? $texto)"></span>
        </button>
    </div>
    <form x-show="abierto" x-cloak x-transition method="POST" action="{{ $action }}" class="mt-2 space-y-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-left">
        @csrf @method('PUT')
        @if ($aviso)<p class="text-xs text-amber-800"><i class="fa-solid fa-triangle-exclamation"></i> {{ $aviso }}</p>@endif
        @if (! $solo && trim($slot) !== '')
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">{{ $slot }}</div>
        @endif
        <div>
            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Motivo de la corrección <span class="text-rose-500">*</span></label>
            <input type="text" name="motivo" required minlength="5" maxlength="500" placeholder="Ej. Se capturó 450 en lugar de 405"
                   class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
        </div>
        <div class="flex flex-wrap justify-end gap-2">
            @if ($solo)
                <button type="submit" data-confirm="{{ $solo }}: ¿confirmas? Quedará registrado." data-peligro data-boton="{{ $solo }}" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700">{{ $solo }}</button>
            @else
                @if ($anular)
                    <button type="submit" name="anular" value="1" formnovalidate
                            onclick="const m = this.form.motivo; if (m.value.trim().length < 5) { m.reportValidity(); return false }" data-confirm="{{ $anular }}: ¿confirmas? Quedará registrado." data-peligro data-boton="{{ $anular }}"
                            class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">{{ $anular }}</button>
                @endif
                <button type="submit" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-600"><i class="fa-solid fa-floppy-disk"></i> Guardar corrección</button>
            @endif
        </div>
    </form>
</div>
