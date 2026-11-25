{{--
    Botón "+ Nuevo" con formulario rápido en ventana. Crea el registro sin salir de la pantalla,
    lo agrega a la lista indicada y lo deja seleccionado.
    Uso: <x-ui.atajo tipo="proveedor" target="#proveedor_id" />
         <x-ui.atajo tipo="insumo" target="select[name=insumo_id]" :valores="['tipo' => 'medicamento']" />
--}}
@props(['tipo', 'target', 'valores' => [], 'texto' => '+ Nuevo', 'seleccionar' => true])
@php $def = \App\Support\Atajos::TIPOS[$tipo]; @endphp
@can($def['permiso'])
<span x-data="{
        abierto: false, enviando: false, errores: {}, campos: @js(collect($def['campos'])->map(fn ($c, $k) => $valores[$k] ?? '')->all()),
        async guardar() {
            this.enviando = true; this.errores = {};
            try {
                const r = await window.axios.post(@js(route('atajos.store', $tipo)), this.campos, { headers: { Accept: 'application/json' } });
                const { id, texto, data } = r.data;
                const selects = [...document.querySelectorAll(@js($target))];
                document.querySelectorAll('template').forEach(t => selects.push(...t.content.querySelectorAll(@js($target))));
                selects.forEach(s => {
                    const op = new Option(texto, id);
                    Object.entries(data || {}).forEach(([k, v]) => op.dataset[k] = v ?? '');
                    s.add(op);
                });
                const principal = document.querySelector(@js($target));
                if (principal && @js((bool) $seleccionar)) { principal.value = id; principal.dispatchEvent(new Event('change', { bubbles: true })); principal.dispatchEvent(new Event('input', { bubbles: true })); }
                window.dispatchEvent(new CustomEvent('atajo-creado', { detail: { tipo: @js($tipo), id, texto, data } }));
                this.abierto = false;
                Object.keys(this.campos).forEach(k => this.campos[k] = @js($valores)[k] ?? '');
            } catch (e) {
                this.errores = e.response?.data?.errors || { general: [e.response?.data?.message || 'No se pudo guardar.'] };
            } finally { this.enviando = false; }
        }
     }" class="inline-block">
    <button type="button" @click="abierto = true" class="text-[11px] font-semibold text-emerald-700 hover:underline">{{ $texto }}</button>

    <template x-teleport="body">
        {{-- @click.stop: evita que el clic "fuera" cierre la ventana que contiene el formulario original --}}
        <div x-show="abierto" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4"
             @click.stop @click.self="abierto = false" @keydown.escape.window.stop="abierto = false">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">{{ $def['titulo'] }}</h3>
                    <button type="button" @click="abierto = false" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form @submit.prevent="guardar()" class="space-y-3">
                    @foreach ($def['campos'] as $nombre => $c)
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">{{ $c[0] }}@if($c[2]) <span class="text-rose-500">*</span>@endif</label>
                            @if ($c[1] === 'select')
                                <select x-model="campos.{{ $nombre }}" @required($c[2]) class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
                                    <option value="">— Selecciona —</option>
                                    @foreach ($c[3] as $k => $t)<option value="{{ $k }}">{{ $t }}</option>@endforeach
                                </select>
                            @else
                                <input type="{{ $c[1] }}" x-model="campos.{{ $nombre }}" @required($c[2]) @if($c[1] === 'number') step="any" @endif
                                       class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm" @if($loop->first) x-effect="abierto && $nextTick(() => $el.focus())" @endif>
                            @endif
                            <template x-if="errores.{{ $nombre }}"><p class="mt-1 text-xs text-rose-600" x-text="errores.{{ $nombre }}[0]"></p></template>
                        </div>
                    @endforeach
                    <template x-if="errores.general"><p class="text-xs text-rose-600" x-text="errores.general[0]"></p></template>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="abierto = false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancelar</button>
                        <button type="submit" :disabled="enviando" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            <span x-show="!enviando">Guardar y seleccionar</span><span x-show="enviando">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</span>
@endcan
