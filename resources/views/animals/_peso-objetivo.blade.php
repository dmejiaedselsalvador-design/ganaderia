{{-- Botón + ventana para configurar el peso objetivo de venta por categoría --}}
@php
    $mapaPesos = \App\Models\Ganaderia\PesoObjetivo::mapa();
    $porCategoria = \App\Models\Ganado\Ganado::activo()->selectRaw('categoria, count(*) as total, sum(case when peso_objetivo > 0 then 1 else 0 end) as propios')
        ->groupBy('categoria')->get()->keyBy('categoria');
    $grupos = ['Machos' => \App\Models\Ganado\Ganado::CATEGORIAS_MACHOS, 'Hembras' => \App\Models\Ganado\Ganado::CATEGORIAS_HEMBRAS];
@endphp
<div x-data="{ abierto: @js($errors->has('pesos.*')) }" class="flex-1 sm:flex-none">
    <button type="button" @click="abierto = true"
            class="w-full bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
        <i class="fa-solid fa-bullseye"></i> Peso objetivo
    </button>

    <template x-teleport="body">
        <div x-show="abierto" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4" @keydown.escape.window="abierto = false" @click.self="abierto = false">
            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900"><i class="fa-solid fa-bullseye text-amber-500"></i> Peso objetivo de venta</h3>
                    <button type="button" @click="abierto = false" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <p class="mb-5 text-xs text-slate-500">Cuando un animal llega a este peso se marca <strong>«Listo venta»</strong>. Aplica a todos los animales de la categoría, salvo los que tengan su propio peso objetivo en su ficha.</p>

                <form method="POST" action="{{ route('ganado.pesos-objetivo') }}" class="space-y-5">
                    @csrf @method('PUT')
                    @foreach ($grupos as $grupo => $categorias)
                        <div>
                            <p class="mb-2 flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider {{ $grupo === 'Machos' ? 'text-blue-600' : 'text-rose-500' }}">
                                <i class="fa-solid {{ $grupo === 'Machos' ? 'fa-mars' : 'fa-venus' }}"></i> {{ $grupo }}
                            </p>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach ($categorias as $c)
                                    @php $info = $porCategoria[$c] ?? null; @endphp
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">{{ $c }}</label>
                                        <div class="relative mt-1">
                                            <input type="number" step="1" min="50" max="1500" name="pesos[{{ $c }}]" required
                                                   value="{{ old('pesos.' . $c, (float) $mapaPesos[$c]) }}"
                                                   class="block w-full rounded-xl border-slate-200 py-2 pr-9 font-mono text-sm">
                                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">kg</span>
                                        </div>
                                        <p class="mt-1 text-[10px] text-slate-400">
                                            {{ $info?->total ?? 0 }} activos @if (($info?->propios ?? 0) > 0) · {{ $info->propios }} con peso propio @endif
                                        </p>
                                        @error('pesos.' . $c)<p class="text-[10px] text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" @click="abierto = false" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600">Cancelar</button>
                        <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
