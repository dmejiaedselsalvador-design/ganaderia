<x-app-layout>
    @php
        $datosInsumos = $insumos->mapWithKeys(fn ($i) => [$i->id => [
            'nombre' => $i->nombre, 'costo' => $i->costoKg(), 'existencia' => max(0, $i->existenciaKg()), 'unidad' => $i->unidad,
            'factor' => $i->kgPorUnidad(), 'kgl' => $i->kgPorLitro(), 'controla' => (bool) $i->controla_existencia,
        ]]);
        $msCalc = $racion->materiaSecaCalculada(); $rango = \App\Models\Engorda\RacionAlimento::RANGOS_PV[$racion->fase] ?? [null, null, null];
        $filas = old('ingredientes', $racion->ingredientes->map(fn ($i) => [
            'insumo_id' => (string) $i->insumo_id, 'porcentaje' => (float) $i->porcentaje,
            'cantidad' => $i->cantidad !== null ? (float) $i->cantidad : '', 'unidad' => $i->unidad_captura ?? $i->insumo?->unidad ?? 'kg',
        ])->values()->all());
        $modo = old('modo', $racion->ingredientes->isEmpty() || $racion->ingredientes->whereNotNull('cantidad')->isNotEmpty() ? 'cantidad' : 'porcentaje');
        $unidadesTexto = ['kg' => 'kg', 'g' => 'gramos', 'lb' => 'libras', 'ton' => 'toneladas', 'l' => 'litros', 'ml' => 'ml', 'saco' => 'sacos', 'pieza' => 'piezas', 'frasco' => 'frascos', 'dosis' => 'dosis'];
    @endphp
    <div class="mx-auto max-w-4xl space-y-6" x-data="{
        modo: @js($modo), filas: @js($filas ?: [['insumo_id' => '', 'porcentaje' => '', 'cantidad' => '', 'unidad' => 'kg']]),
        insumos: @js($datosInsumos), bache: 1000, textoUnidad: @js($unidadesTexto),
        pesoFijo: { kg: 1, g: 0.001, lb: 0.45359237, ton: 1000 },
        unidades(f) { const i = this.insumos[f.insumo_id]; return i ? [...new Set(['kg', 'g', 'lb', 'ton', 'l', 'ml', i.unidad])] : ['kg'] },
        kgCaptura(f) {
            const i = this.insumos[f.insumo_id], c = Number(f.cantidad || 0);
            if (!i || !c) return 0;
            if (this.pesoFijo[f.unidad]) return c * this.pesoFijo[f.unidad];
            if (f.unidad === 'l') return c * i.kgl;
            if (f.unidad === 'ml') return c * i.kgl / 1000;
            return c * i.factor;
        },
        get rinde() { return this.filas.reduce((a, f) => a + this.kgCaptura(f), 0) },
        pct(f) { return this.modo === 'cantidad' ? (this.rinde > 0 ? this.kgCaptura(f) / this.rinde * 100 : 0) : Number(f.porcentaje || 0) },
        get suma() { return this.filas.reduce((a, f) => a + this.pct(f), 0) },
        kg(f) { return this.bache * this.pct(f) / 100 },
        costo(f) { const i = this.insumos[f.insumo_id]; return i ? this.kg(f) * i.costo : 0 },
        get costoTotal() { return this.filas.reduce((a, f) => a + this.costo(f), 0) },
        num(v, d = 2) { return Number(v).toLocaleString('es-MX', { maximumFractionDigits: d }) },
        money(v) { return '$ ' + Number(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) },
        elegido(f) { const i = this.insumos[f.insumo_id]; if (i) f.unidad = i.unidad },
        cambiarModo(m) {
            if (m === this.modo) return;
            if (m === 'porcentaje') this.filas.forEach(f => f.porcentaje = Number(this.pct(f).toFixed(3)));
            else this.filas.forEach(f => { if (!f.cantidad) { f.unidad = 'kg'; f.cantidad = Number(f.porcentaje || 0) } });
            this.modo = m;
        },
        nuevoInsumo(d) {
            if (d.tipo !== 'insumo') return;
            this.insumos[d.id] = { nombre: d.data.nombre, costo: 0, existencia: 0, unidad: d.data.unidad, factor: Number(d.data.factor || 1), kgl: Number(d.data.kgl || 1), controla: d.data.controla == 1 };
            let f = this.filas.find(f => !f.insumo_id);
            if (!f) { f = { insumo_id: '', porcentaje: '', cantidad: '', unidad: 'kg' }; this.filas.push(f) }
            f.insumo_id = String(d.id); f.unidad = d.data.unidad;
        } }"
        x-init="if (modo === 'cantidad' && rinde > 0) bache = Number(rinde.toFixed(3))"
        x-on:atajo-creado.window="nuevoInsumo($event.detail)">
        <x-ui.header :titulo="'Receta: ' . $racion->nombre" :subtitulo="$racion->codigo . ' · costo actual $ ' . number_format((float) $racion->costo_por_kg, 2) . ' / kg'" seccion="Nutrición" icono="fa-flask">
            <x-slot name="acciones">
                @if ($volver)<x-ui.btn :href="$volver" variante="secundario" icono="fa-arrow-left">Regresar</x-ui.btn>@endif
                <x-ui.btn :href="route('nutricion.index')" variante="secundario" icono="fa-wheat-awn">Nutrición</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <i class="fa-solid fa-circle-info"></i> La receta liga la dieta con los artículos de bodega. Cada vez que se registra alimento servido con esta dieta, el sistema <strong>descuenta del inventario</strong> lo que corresponde de cada ingrediente.
        </div>

        <x-ui.card titulo="Receta / BOM de la dieta" subtitulo="Captura cómo se prepara: por cantidades (como en la libreta) o directamente en porcentajes">
            <form method="POST" action="{{ route('raciones.formula.update', $racion) }}" class="space-y-3">
                @csrf @method('PUT')
                @if ($volver)<input type="hidden" name="volver" value="{{ $volver }}">@endif
                <input type="hidden" name="modo" :value="modo">

                <div class="mb-2 inline-flex rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs font-semibold">
                    <button type="button" @click="cambiarModo('cantidad')" class="rounded-lg px-3 py-1.5" :class="modo === 'cantidad' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"><i class="fa-solid fa-scale-balanced"></i> Por cantidades</button>
                    <button type="button" @click="cambiarModo('porcentaje')" class="rounded-lg px-3 py-1.5" :class="modo === 'porcentaje' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'"><i class="fa-solid fa-percent"></i> Por porcentaje</button>
                </div>
                <p x-show="modo === 'cantidad'" class="text-xs text-slate-500">Ejemplo: «Engorda ternero» = 2 kg de maíz + 2 litros de agua + 2 libras de sal. El sistema convierte todo a kg y calcula el % de cada ingrediente.</p>

                <template x-for="(f, i) in filas" :key="i">
                    <div class="rounded-xl border border-slate-100 p-3">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-[12rem] flex-1">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Ingrediente</label>
                                <select data-ingrediente :name="'ingredientes[' + i + '][insumo_id]'" x-model="f.insumo_id" @change="elegido(f)" required class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
                                    <option value="">— Selecciona —</option>
                                    @foreach ($insumos as $ins)
                                        <option value="{{ $ins->id }}">{{ $ins->nombre }} ({{ $ins->unidad }}{{ $ins->controla_existencia ? '' : ' · sin control de bodega' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <template x-if="modo === 'cantidad'">
                                <div class="flex items-end gap-2">
                                    <div class="w-28">
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Cantidad</label>
                                        <input type="number" step="any" min="0" :name="'ingredientes[' + i + '][cantidad]'" x-model="f.cantidad" required class="mt-1 block w-full rounded-xl border-slate-200 py-2 font-mono text-sm">
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Unidad</label>
                                        <select :name="'ingredientes[' + i + '][unidad]'" x-model="f.unidad" class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
                                            <template x-for="u in unidades(f)" :key="u"><option :value="u" x-text="textoUnidad[u] || u" :selected="u === f.unidad"></option></template>
                                        </select>
                                    </div>
                                </div>
                            </template>
                            <template x-if="modo === 'porcentaje'">
                                <div class="w-32">
                                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">%</label>
                                    <input type="number" step="0.001" min="0" max="100" :name="'ingredientes[' + i + '][porcentaje]'" x-model="f.porcentaje" required class="mt-1 block w-full rounded-xl border-slate-200 py-2 font-mono text-sm">
                                </div>
                            </template>
                            <button type="button" @click="filas.splice(i, 1)" class="mb-1 flex h-9 w-9 items-center justify-center rounded-lg text-rose-500 hover:bg-rose-50" title="Quitar"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <p x-show="modo === 'cantidad' && f.insumo_id && f.cantidad" class="mt-1.5 text-[11px] text-slate-500">
                            = <strong class="font-mono" x-text="num(kgCaptura(f), 3) + ' kg'"></strong> · <strong class="font-mono" x-text="num(pct(f), 2) + ' %'"></strong> de la receta
                            <span x-show="(f.unidad === 'l' || f.unidad === 'ml') && insumos[f.insumo_id]?.unidad !== 'l' && insumos[f.insumo_id]?.unidad !== 'ml'" class="text-amber-600">(se toma 1 litro = 1 kg)</span>
                            <span x-show="insumos[f.insumo_id] && !insumos[f.insumo_id].controla" class="text-slate-400">· no se descuenta de bodega</span>
                        </p>
                    </div>
                </template>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3">
                    <div class="flex flex-wrap items-center gap-4">
                        <button type="button" @click="filas.push({ insumo_id: '', porcentaje: '', cantidad: '', unidad: 'kg' })" class="text-sm font-semibold text-emerald-700"><i class="fa-solid fa-plus"></i> Agregar ingrediente</button>
                        <x-ui.atajo tipo="insumo" target="select[data-ingrediente]" :valores="['tipo' => 'alimento', 'unidad' => 'kg', 'controla_existencia' => '1']" :seleccionar="false" texto="¿No está en la lista? Crear ingrediente" />
                    </div>
                    <p x-show="modo === 'cantidad'" class="text-sm font-semibold text-emerald-600">Rinde: <span class="font-mono" x-text="num(rinde, 3)"></span> kg por bache</p>
                    <p x-show="modo === 'porcentaje'" class="text-sm font-semibold" :class="Math.abs(suma - 100) <= 0.5 ? 'text-emerald-600' : 'text-rose-600'">Total: <span x-text="suma.toFixed(2)"></span>%</p>
                </div>
                @if ($insumos->isEmpty())
                    <p class="text-xs text-amber-700">Aún no hay ingredientes en bodega. Usa «Crear ingrediente» aquí mismo; después registra sus entradas en <a href="{{ route('inventario.index') }}" class="font-semibold underline">Inventario</a>.</p>
                @endif
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="actualizar_costo" value="1" checked class="rounded border-slate-300 text-emerald-600"> Actualizar el costo por kg de la dieta con el costo promedio de los ingredientes</label>
                <x-ui.btn icono="fa-floppy-disk">Guardar receta</x-ui.btn>
            </form>
        </x-ui.card>

        <!-- Parámetros de consumo por peso vivo -->
        <x-ui.card titulo="Consumo por peso vivo" subtitulo="Rango que usa el plan de alimentación para calcular kg por cabeza a partir del peso de cada animal">
            <form method="POST" action="{{ route('raciones.consumo.update', $racion) }}" class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @csrf @method('PUT')
                @if ($volver)<input type="hidden" name="volver" value="{{ $volver }}">@endif
                <div class="sm:col-span-1">
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Base</label>
                    <select name="base_consumo" class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
                        <option value="MS" @selected(old('base_consumo', $racion->base_consumo) === 'MS')>Materia seca</option>
                        <option value="TCO" @selected(old('base_consumo', $racion->base_consumo) === 'TCO')>Tal como se ofrece</option>
                    </select>
                </div>
                @foreach (['pct_pv_min' => ['% PV mínimo', 0], 'pct_pv_sugerido' => ['% PV sugerido', 2], 'pct_pv_max' => ['% PV máximo', 1]] as $campo => [$etq, $idx])
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">{{ $etq }}</label>
                        <input type="number" step="0.01" min="0.5" max="5" name="{{ $campo }}" value="{{ old($campo, $racion->$campo ?? $rango[$idx]) }}" class="mt-1 block w-full rounded-xl border-slate-200 py-2 font-mono text-sm">
                    </div>
                @endforeach
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">% materia seca</label>
                    <input type="number" step="0.01" min="10" max="100" name="materia_seca_pct" value="{{ old('materia_seca_pct', $racion->materia_seca_pct) }}" class="mt-1 block w-full rounded-xl border-slate-200 py-2 font-mono text-sm">
                    @if ($msCalc)<p class="mt-1 text-[11px] text-slate-500">Según ingredientes: <strong>{{ number_format($msCalc, 1) }}%</strong></p>@endif
                </div>
                <div class="flex items-end"><x-ui.btn icono="fa-floppy-disk">Guardar</x-ui.btn></div>
            </form>
            <p class="mt-3 text-xs text-slate-500">Ejemplo: un novillo de 400 kg al {{ number_format((float) ($racion->pct_pv_sugerido ?? $rango[2] ?? 2.5), 1) }}% come
                {{ number_format(400 * (float) ($racion->pct_pv_sugerido ?? $rango[2] ?? 2.5) / 100, 1) }} kg de materia seca al día
                @if (($msCalc ?? $racion->materia_seca_pct) > 0)
                    = <strong>{{ number_format(400 * (float) ($racion->pct_pv_sugerido ?? $rango[2] ?? 2.5) / 100 / (($msCalc ?? (float) $racion->materia_seca_pct) / 100), 1) }} kg</strong> de esta dieta tal como se sirve.
                @endif
            </p>
        </x-ui.card>

        <!-- Explosión de materiales para un bache -->
        <x-ui.card titulo="Explosión de materiales por bache" subtitulo="Cuánto de cada ingrediente se necesita para preparar la cantidad indicada, su costo y si alcanza la bodega">
            <div class="mb-4 flex items-end gap-3">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Kg a preparar</label>
                    <input type="number" min="0.001" step="any" x-model.number="bache" class="mt-1 w-40 rounded-xl border-slate-200 py-2 font-mono text-sm">
                </div>
                <button type="button" x-show="modo === 'cantidad' && rinde > 0" @click="bache = Number(rinde.toFixed(3))" class="pb-2 text-xs font-semibold text-emerald-700 hover:underline">Usar lo que rinde la receta</button>
                <p class="pb-2 text-sm text-slate-500">Costo del bache: <strong class="font-mono text-slate-800" x-text="money(costoTotal)"></strong> · por kg: <strong class="font-mono text-slate-800" x-text="money(bache > 0 ? costoTotal / bache : 0)"></strong></p>
            </div>
            <table class="w-full text-sm">
                <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-3 py-2">Ingrediente</th><th class="px-3 text-right">%</th><th class="px-3 text-right">Kg</th><th class="px-3 text-right">Costo</th><th class="px-3 text-right">En bodega</th><th class="px-3">Estado</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(f, i) in filas" :key="'b' + i">
                        <tr x-show="f.insumo_id">
                            <td class="px-3 py-2 font-semibold text-slate-700" x-text="insumos[f.insumo_id]?.nombre"></td>
                            <td class="px-3 text-right font-mono" x-text="pct(f).toFixed(2)"></td>
                            <td class="px-3 text-right font-mono" x-text="kg(f).toLocaleString('es-MX', { maximumFractionDigits: 1 })"></td>
                            <td class="px-3 text-right font-mono" x-text="money(costo(f))"></td>
                            <td class="px-3 text-right font-mono" x-text="(insumos[f.insumo_id]?.existencia ?? 0).toLocaleString('es-MX', { maximumFractionDigits: 0 })"></td>
                            <td class="px-3 text-xs font-semibold" x-show="insumos[f.insumo_id] && !insumos[f.insumo_id].controla"><span class="text-slate-400">No se controla</span></td>
                            <td x-show="insumos[f.insumo_id]?.controla" class="px-3 text-xs font-semibold" :class="(insumos[f.insumo_id]?.existencia ?? 0) >= kg(f) ? 'text-emerald-600' : 'text-rose-600'"
                                x-text="(insumos[f.insumo_id]?.existencia ?? 0) >= kg(f) ? 'Alcanza' : 'Faltan ' + (kg(f) - (insumos[f.insumo_id]?.existencia ?? 0)).toLocaleString('es-MX', { maximumFractionDigits: 0 }) + ' kg'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p class="mt-3 text-xs text-slate-500">Para ver el consumo de todos los corrales y los días de cobertura de bodega usa <a href="{{ route('nutricion.planeacion') }}" class="font-semibold text-emerald-700 underline">Plan de alimentación</a>.</p>
        </x-ui.card>
    </div>
</x-app-layout>
