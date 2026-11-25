<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Planeación de alimento" subtitulo="Explosión de materiales (BOM) de las dietas: lo que cada corral consume al día y para cuántos días alcanza la bodega." seccion="Nutrición" icono="fa-diagram-project">
            <x-slot name="acciones">
                <form method="GET" class="flex items-end gap-2">
                    <x-ui.field label="Planear para (días)" name="dias" type="number" :value="$dias" min="1" max="90" />
                    <x-ui.btn variante="oscuro" icono="fa-calculator">Calcular</x-ui.btn>
                </form>
            </x-slot>
        </x-ui.header>

        <x-ui.card titulo="Consumo por corral" subtitulo="Ración = la del último servido registrado · consumo = promedio de los últimos 7 días, proyectado a las cabezas actuales" :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Corral</th><th class="px-3">Dieta</th><th class="px-3 text-right">Cabezas</th><th class="px-3 text-right">kg/cab/día</th><th class="px-3 text-right">kg/día</th><th class="px-3 text-right">Costo/día</th><th class="px-3 text-right">Costo/cab/día</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($lineas as $l)
                            <tr>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $l['corral']->codigo }}<span class="block text-[11px] font-normal text-slate-400">{{ $l['corral']->nombre }}</span></td>
                                <td class="px-3 text-xs">
                                    @if ($l['racion'])
                                        <a href="{{ route('raciones.formula', $l['racion']) }}" class="font-semibold text-emerald-700 hover:underline">{{ $l['racion']->codigo }}</a>
                                        @if ($l['sin_formula'] ?? false)<span class="block text-[10px] font-semibold text-amber-600">Sin receta (BOM): no se explota</span>@endif
                                    @else <span class="text-slate-400">Sin servidos registrados</span> @endif
                                </td>
                                <td class="px-3 text-right font-mono">{{ $l['cabezas'] }}</td>
                                <td class="px-3 text-right font-mono">{{ $l['kg_cab_dia'] !== null ? number_format($l['kg_cab_dia'], 2) : '—' }}</td>
                                <td class="px-3 text-right font-mono font-semibold">{{ number_format($l['kg_dia'], 0) }}</td>
                                <td class="px-3 text-right font-mono">{{ formatoPesos($l['costo_dia']) }}</td>
                                <td class="px-3 text-right font-mono text-xs text-slate-500">{{ $l['cabezas'] ? formatoPesos($l['costo_dia'] / $l['cabezas']) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="bg-slate-50 font-semibold"><td class="px-4 py-2.5" colspan="4">Total diario</td>
                        <td class="px-3 text-right font-mono">{{ number_format(collect($lineas)->sum('kg_dia'), 0) }} kg</td>
                        <td class="px-3 text-right font-mono">{{ formatoPesos(collect($lineas)->sum('costo_dia')) }}</td><td></td></tr></tfoot>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card titulo="Explosión de materiales para {{ $dias }} días" subtitulo="Necesidad de cada ingrediente de bodega según las recetas de las dietas" :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Ingrediente</th><th class="px-3 text-right">kg/día</th><th class="px-3 text-right">Requerido {{ $dias }} d</th><th class="px-3 text-right">En bodega</th><th class="px-3 text-right">Alcanza para</th><th class="px-3 text-right">Faltante</th><th class="px-3 text-right">Compra estimada</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($explosion as $e)
                            <tr class="{{ $e['faltante'] > 0 ? 'bg-rose-50/40' : '' }}">
                                <td class="px-4 py-2.5"><a href="{{ route('inventario.show', $e['insumo']) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $e['insumo']->nombre }}</a></td>
                                <td class="px-3 text-right font-mono">{{ number_format($e['kg_dia'], 1) }}</td>
                                <td class="px-3 text-right font-mono">{{ number_format($e['requerido'], 0) }}</td>
                                <td class="px-3 text-right font-mono">{{ number_format($e['existencia'], 0) }}</td>
                                <td class="px-3 text-right font-mono font-semibold {{ ($e['cobertura'] ?? 99) < $dias ? 'text-rose-600' : 'text-emerald-600' }}">{{ $e['cobertura'] !== null ? $e['cobertura'] . ' días' : '—' }}</td>
                                <td class="px-3 text-right font-mono {{ $e['faltante'] > 0 ? 'font-bold text-rose-600' : 'text-slate-400' }}">{{ $e['faltante'] > 0 ? number_format($e['faltante'], 0) . ' kg' : '—' }}</td>
                                <td class="px-3 text-right font-mono">{{ $e['costo_compra'] > 0 ? formatoPesos($e['costo_compra']) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">Para explotar materiales, captura la receta (BOM) de cada dieta y registra servidos de alimento a los corrales.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($explosion->isNotEmpty())
                        <tfoot><tr class="bg-slate-50 font-semibold"><td class="px-4 py-2.5" colspan="6">Compra estimada total</td><td class="px-3 text-right font-mono">{{ formatoPesos($explosion->sum('costo_compra')) }}</td></tr></tfoot>
                    @endif
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
