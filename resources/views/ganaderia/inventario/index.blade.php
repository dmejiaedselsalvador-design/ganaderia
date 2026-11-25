@php $tipos = \App\Models\Ganaderia\Insumo::TIPOS; $unidades = array_combine(\App\Models\Ganaderia\Insumo::UNIDADES, \App\Models\Ganaderia\Insumo::UNIDADES); @endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ nuevo: {{ $errors->any() ? 'true' : 'false' }} }">
        <x-ui.header titulo="Inventario de insumos" subtitulo="Alimento, ingredientes y medicamentos con kardex, costo promedio, lotes y caducidades." icono="fa-boxes-stacked">
            <x-slot name="acciones">
                <x-ui.btn :href="request()->fullUrlWithQuery(['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Exportar Excel</x-ui.btn>
                <x-ui.btn type="button" x-on:click="nuevo = !nuevo" icono="fa-plus">Nuevo insumo</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <x-ui.card titulo="Nuevo insumo" x-show="nuevo" x-cloak>
            <form method="POST" action="{{ route('inventario.store') }}" class="grid gap-3 sm:grid-cols-4">
                @csrf
                <x-ui.field label="Código" name="codigo" required placeholder="MAIZ-01" />
                <x-ui.field label="Nombre" name="nombre" required colspan="sm:col-span-2" placeholder="Maíz amarillo quebrado" />
                <x-ui.field label="Tipo" name="tipo" type="select" :options="$tipos" required :placeholder="false" />
                <x-ui.field label="Unidad" name="unidad" type="select" :options="$unidades" required :placeholder="false" ayuda="Alimentos en kg o ton para que se descuenten de las raciones." />
                <x-ui.field label="Stock mínimo" name="stock_minimo" type="number" step="0.001" />
                <x-ui.field label="Días de retiro" name="dias_retiro" type="number" ayuda="Solo medicamentos" />
                <x-ui.field label="% materia seca" name="materia_seca_pct" type="number" step="0.01" ayuda="Solo alimentos. Ej. maíz 88, silo 35" />
                <x-ui.field label="Kg por unidad" name="factor_kg" type="number" step="0.001" ayuda="Saco o paca: kg que trae cada uno. Litros: kg que pesa un litro (agua 1, melaza 1.4). kg, lb y ton se calculan solos." />
                <label class="flex items-center gap-2 self-end pb-2 text-xs text-slate-600"><input type="checkbox" name="sin_existencia" value="1" class="rounded border-slate-300"> No llevar existencia en bodega (ej. agua de pozo)</label>
                <x-ui.field label="Presentación" name="presentacion" placeholder="Frasco 500 ml, saco 40 kg..." />
                <div class="sm:col-span-4"><x-ui.btn icono="fa-floppy-disk">Crear insumo</x-ui.btn></div>
            </form>
        </x-ui.card>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.kpi label="Valor del inventario" :valor="formatoPesos($valorTotal)" icono="fa-warehouse" />
            <x-ui.kpi label="Bajo mínimo" :valor="$bajoMinimo->count()" :nota="$bajoMinimo->pluck('nombre')->take(3)->implode(', ')" icono="fa-arrow-trend-down" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Lotes por caducar (30 días)" :valor="$porCaducar->count()" icono="fa-hourglass-end" color="bg-rose-50 text-rose-600" />
        </div>

        @if ($porCaducar->isNotEmpty())
            <x-ui.card titulo="Lotes por caducar o caducados">
                <div class="flex flex-wrap gap-2">
                    @foreach ($porCaducar as $m)
                        <span class="rounded-lg border px-3 py-1.5 text-xs {{ $m->fecha_caducidad->isPast() ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                            <strong>{{ $m->insumo->nombre }}</strong> · lote {{ $m->lote ?: 's/n' }} · {{ $m->fecha_caducidad->isPast() ? 'caducó' : 'caduca' }} {{ $m->fecha_caducidad->format('d/m/Y') }}
                        </span>
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex-1"><x-ui.field label="Buscar" name="q" :value="request('q')" placeholder="Nombre o código" /></div>
            <x-ui.field label="Tipo" name="tipo" type="select" :options="$tipos" :value="request('tipo')" placeholder="Todos" />
            <x-ui.btn variante="oscuro" icono="fa-filter">Filtrar</x-ui.btn>
        </form>

        <x-ui.card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Insumo</th><th class="px-3">Tipo</th><th class="px-3 text-right">Existencia</th><th class="px-3 text-right">Costo prom.</th><th class="px-3 text-right">Valor</th><th class="px-3">Estado</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($insumos as $i)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5"><a href="{{ route('inventario.show', $i) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $i->nombre }}</a><p class="font-mono text-[11px] text-slate-400">{{ $i->codigo }}</p></td>
                                <td class="px-3 text-xs text-slate-500">{{ $tipos[$i->tipo] ?? $i->tipo }}</td>
                                <td class="px-3 text-right font-mono {{ (float) $i->existencia < 0 ? 'text-rose-600 font-bold' : 'text-slate-700' }}">{{ number_format((float) $i->existencia, 2) }} {{ $i->unidad }}</td>
                                <td class="px-3 text-right font-mono text-xs text-slate-500">$ {{ number_format((float) $i->costo_promedio, 2) }}</td>
                                <td class="px-3 text-right font-mono text-xs text-slate-700">{{ formatoPesos($i->valorInventario()) }}</td>
                                <td class="px-3 text-xs">
                                    @if ((float) $i->existencia < 0)<span class="font-semibold text-rose-600">Negativo: registra entrada</span>
                                    @elseif ($i->estaBajoMinimo())<span class="font-semibold text-amber-600">Bajo mínimo</span>
                                    @else<span class="text-emerald-600">OK</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Aún no hay insumos. Crea el primero con «Nuevo insumo».</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
