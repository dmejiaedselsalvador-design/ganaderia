{{-- Cruce del requerimiento con la bodega. Recibe $bodega y $dias. --}}
@php $estados = ['ok' => ['Alcanza', 'bg-emerald-50 text-emerald-700 border-emerald-200'], 'justo' => ['Margen justo', 'bg-amber-50 text-amber-700 border-amber-200'], 'faltante' => ['Faltante', 'bg-rose-50 text-rose-700 border-rose-200']]; @endphp
<x-ui.card titulo="Explosión de materiales y bodega · {{ $dias }} {{ $dias === 1 ? 'día' : 'días' }}" subtitulo="Lo que piden las recetas (BOM) contra lo que hay en bodega. Margen justo = sobra menos del 10 %." :padding="false">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <th class="px-4 py-2.5">Ingrediente</th><th class="px-3 text-right">kg por día</th><th class="px-3 text-right">Requerido</th>
                <th class="px-3 text-right">En bodega</th><th class="px-3 text-right">Alcanza para</th><th class="px-3 text-right">Faltante</th><th class="px-3 text-right">Compra estimada</th><th class="px-3">Estado</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($bodega as $l)
                    <tr class="{{ $l['estado'] === 'faltante' ? 'bg-rose-50/40' : '' }}">
                        <td class="px-4 py-2.5">
                            @can('ganaderia.inventario')
                                <a href="{{ route('inventario.show', $l['insumo']) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $l['insumo']->nombre }}</a>
                            @else <span class="font-semibold text-slate-800">{{ $l['insumo']->nombre }}</span> @endcan
                            @if ($l['negativo'])<span class="block text-[10px] font-semibold text-rose-600">Existencia negativa: falta registrar entradas</span>@endif
                        </td>
                        <td class="px-3 text-right font-mono">{{ number_format($l['kg'] / max(1, $dias), 1) }}</td>
                        <td class="px-3 text-right font-mono font-semibold">{{ number_format($l['kg'], 0) }} kg</td>
                        <td class="px-3 text-right font-mono">{{ number_format($l['disponible_kg'], 0) }} kg</td>
                        <td class="px-3 text-right font-mono {{ ($l['cobertura'] ?? 999) < $dias ? 'font-bold text-rose-600' : 'text-slate-600' }}">{{ $l['cobertura'] !== null ? $l['cobertura'] . ' días' : '—' }}</td>
                        <td class="px-3 text-right font-mono {{ $l['faltante_kg'] > 0 ? 'font-bold text-rose-600' : 'text-slate-300' }}">{{ $l['faltante_kg'] > 0 ? number_format($l['faltante_kg'], 0) . ' kg' : '—' }}</td>
                        <td class="px-3 text-right font-mono">{{ $l['costo_compra'] > 0 ? formatoPesos($l['costo_compra']) : '—' }}</td>
                        <td class="px-3"><span class="rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $estados[$l['estado']][1] }}">{{ $estados[$l['estado']][0] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">Asigna una dieta con receta completa a los corrales para calcular la explosión de materiales.</td></tr>
                @endforelse
            </tbody>
            @if ($bodega->isNotEmpty())
                <tfoot><tr class="bg-slate-50 font-semibold">
                    <td class="px-4 py-2.5" colspan="2">Totales</td>
                    <td class="px-3 text-right font-mono">{{ number_format($bodega->sum('kg'), 0) }} kg</td>
                    <td colspan="3"></td>
                    <td class="px-3 text-right font-mono">{{ formatoPesos($bodega->sum('costo_compra')) }}</td><td></td>
                </tr></tfoot>
            @endif
        </table>
    </div>
</x-ui.card>
