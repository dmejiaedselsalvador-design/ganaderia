@php $tipos = \App\Models\Ganaderia\Insumo::TIPOS; $unidades = array_combine(\App\Models\Ganaderia\Insumo::UNIDADES, \App\Models\Ganaderia\Insumo::UNIDADES); @endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ tipo: '{{ old('tipo', 'entrada') }}', editar: false }">
        <x-ui.header :titulo="$insumo->nombre" :subtitulo="$insumo->codigo . ' · ' . ($tipos[$insumo->tipo] ?? '') . ($insumo->presentacion ? ' · ' . $insumo->presentacion : '')" seccion="Kardex de inventario" icono="fa-boxes-stacked">
            <x-slot name="acciones">
                <x-ui.btn :href="request()->fullUrlWithQuery(['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Kardex Excel</x-ui.btn>
                <x-ui.btn :href="route('inventario.index')" variante="secundario" icono="fa-arrow-left">Inventario</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-ui.kpi label="Existencia" :valor="number_format((float) $insumo->existencia, 2) . ' ' . $insumo->unidad" icono="fa-cubes" :valorClass="(float) $insumo->existencia < 0 ? 'text-rose-600' : 'text-slate-900'" />
            <x-ui.kpi label="Costo promedio" :valor="'$ ' . number_format((float) $insumo->costo_promedio, 2) . ' / ' . $insumo->unidad" icono="fa-scale-balanced" color="bg-blue-50 text-blue-600" />
            <x-ui.kpi label="Valor" :valor="formatoPesos($insumo->valorInventario())" icono="fa-sack-dollar" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Stock mínimo" :valor="number_format((float) $insumo->stock_minimo, 2) . ' ' . $insumo->unidad" :nota="$insumo->estaBajoMinimo() ? '¡Reabastecer!' : ''" icono="fa-bell" color="bg-rose-50 text-rose-600" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-ui.card titulo="Registrar movimiento">
                <form method="POST" action="{{ route('inventario.movimiento', $insumo) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1 text-xs font-semibold">
                        @foreach (['entrada' => 'Entrada / compra', 'salida' => 'Salida', 'ajuste' => 'Conteo físico'] as $k => $t)
                            <label class="cursor-pointer"><input type="radio" name="tipo" value="{{ $k }}" x-model="tipo" class="peer sr-only">
                                <span class="block rounded-lg py-1.5 text-center text-slate-500 peer-checked:bg-white peer-checked:text-slate-900 peer-checked:shadow-sm">{{ $t }}</span></label>
                        @endforeach
                    </div>
                    <x-ui.field label="Fecha" name="fecha" type="date" :value="date('Y-m-d')" required />
                    <div>
                        <x-ui.field label="Cantidad ({{ $insumo->unidad }})" name="cantidad" type="number" step="0.001" required />
                        <p x-show="tipo === 'ajuste'" class="mt-1 text-[11px] text-slate-500">Escribe la existencia que contaste físicamente; el sistema registra la diferencia.</p>
                    </div>
                    <div x-show="tipo === 'entrada'" class="space-y-3">
                        <x-ui.field label="Costo unitario ($ por {{ $insumo->unidad }})" name="costo_unitario" type="number" step="0.0001" />
                        <x-ui.field label="Proveedor" name="proveedor" />
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.field label="Lote" name="lote" />
                            <x-ui.field label="Caducidad" name="fecha_caducidad" type="date" />
                        </div>
                    </div>
                    <div x-show="tipo === 'salida'"><x-ui.field label="Corral destino" name="corral_lote_id" type="select" :options="$corrales" placeholder="—" /></div>
                    <x-ui.field label="Referencia" name="referencia" placeholder="Factura, vale, motivo..." />
                    <x-ui.btn icono="fa-check" class="w-full">Registrar</x-ui.btn>
                </form>

                <div class="mt-5 border-t border-slate-100 pt-4">
                    <button type="button" @click="editar = !editar" class="text-xs font-semibold text-emerald-700"><i class="fa-solid fa-pen"></i> Editar datos del insumo</button>
                    <form x-show="editar" x-cloak method="POST" action="{{ route('inventario.update', $insumo) }}" class="mt-3 space-y-3">
                        @csrf @method('PUT')
                        <x-ui.field label="Código" name="codigo" :value="$insumo->codigo" required />
                        <x-ui.field label="Nombre" name="nombre" :value="$insumo->nombre" required />
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.field label="Tipo" name="tipo" type="select" :options="$tipos" :value="$insumo->tipo" :placeholder="false" />
                            <x-ui.field label="Unidad" name="unidad" type="select" :options="$unidades" :value="$insumo->unidad" :placeholder="false" />
                            <x-ui.field label="Stock mínimo" name="stock_minimo" type="number" step="0.001" :value="$insumo->stock_minimo" />
                            <x-ui.field label="Días de retiro" name="dias_retiro" type="number" :value="$insumo->dias_retiro" />
                <x-ui.field label="% materia seca" name="materia_seca_pct" type="number" step="0.01" :value="$insumo->materia_seca_pct" ayuda="Solo alimentos. Ej. maíz 88, silo 35" />
                <x-ui.field label="Kg por unidad" name="factor_kg" type="number" step="0.001" :value="(float) $insumo->factor_kg" ayuda="Saco o paca: kg que trae cada uno. Litros: kg que pesa un litro (agua 1, melaza 1.4). kg, lb y ton se calculan solos." />
                <label class="flex items-center gap-2 self-end pb-2 text-xs text-slate-600"><input type="checkbox" name="sin_existencia" value="1" @checked(! $insumo->controla_existencia) class="rounded border-slate-300"> No llevar existencia en bodega (ej. agua de pozo)</label>
                        </div>
                        <x-ui.field label="Presentación" name="presentacion" :value="$insumo->presentacion" />
                        <label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="activo" value="1" @checked($insumo->activo) class="rounded border-slate-300"> Activo</label>
                        <x-ui.btn variante="oscuro" icono="fa-floppy-disk">Guardar</x-ui.btn>
                    </form>
                </div>
            </x-ui.card>

            <x-ui.card titulo="Kardex" class="lg:col-span-2" :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead><tr class="bg-slate-50 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-2.5">Fecha</th><th class="px-2">Movimiento</th><th class="px-2 text-right">Cantidad</th><th class="px-2 text-right">Costo</th><th class="px-2 text-right">Existencia</th><th class="px-2">Lote / Ref.</th><th class="px-2">Usuario</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($movs as $m)
                                @php $signo = $m->tipo === 'salida' ? -1 : 1; $cant = $signo * (float) $m->cantidad; @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-2 text-slate-500">{{ $m->fecha->format('d/m/Y') }}</td>
                                    <td class="px-2"><span class="rounded-full px-2 py-0.5 font-semibold {{ $m->tipo === 'entrada' ? 'bg-emerald-50 text-emerald-700' : ($m->tipo === 'salida' ? 'bg-rose-50 text-rose-700' : 'bg-blue-50 text-blue-700') }}">{{ ucfirst($m->tipo) }}</span></td>
                                    <td class="px-2 text-right font-mono font-semibold {{ $cant < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $cant > 0 ? '+' : '' }}{{ number_format($cant, 2) }}</td>
                                    <td class="px-2 text-right font-mono text-slate-500">$ {{ number_format((float) $m->costo_unitario, 2) }}</td>
                                    <td class="px-2 text-right font-mono text-slate-700">{{ number_format((float) $m->existencia_resultante, 2) }}</td>
                                    <td class="px-2 text-slate-500">{{ $m->lote ? 'Lote ' . $m->lote : '' }}{{ $m->fecha_caducidad ? ' · cad. ' . $m->fecha_caducidad->format('d/m/Y') : '' }}<br>{{ $m->referencia }}{{ $m->corral ? ' · ' . $m->corral->codigo : '' }}</td>
                                    <td class="px-2 text-slate-400">{{ $m->usuario->name ?? '' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Sin movimientos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($movs->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $movs->links() }}</div>@endif
            </x-ui.card>
        </div>
    </div>
</x-app-layout>
