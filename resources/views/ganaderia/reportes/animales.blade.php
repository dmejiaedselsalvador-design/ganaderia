<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Rentabilidad por animal" subtitulo="Ordenado de menor a mayor utilidad: arriba están los que pierden dinero." seccion="Reportes" icono="fa-cow">
            <x-slot name="acciones"><x-ui.btn :href="request()->fullUrlWithQuery(['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Excel</x-ui.btn></x-slot>
        </x-ui.header>

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <x-ui.field label="Estatus" name="estado" type="select" :options="['Activo' => 'Activos (proyectado)', 'Vendido' => 'Vendidos (real)', 'Muerto' => 'Muertos', 'Baja' => 'Otras bajas', 'todos' => 'Todos']" :value="$estado" :placeholder="false" />
            <x-ui.field label="Corral" name="corral" type="select" :options="$corrales" :value="request('corral')" placeholder="Todos" />
            <x-ui.btn variante="oscuro" icono="fa-filter">Filtrar</x-ui.btn>
        </form>

        <x-ui.card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead><tr class="bg-slate-50 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Arete</th><th class="px-2 text-right">Días</th><th class="px-2 text-right">Compra</th><th class="px-2 text-right">Alimento</th><th class="px-2 text-right">Medicinas</th>
                        <th class="px-2 text-right">Gastos</th><th class="px-2 text-right">Costo total</th><th class="px-2 text-right">Kg ganados</th><th class="px-2 text-right">$/kg</th><th class="px-2 text-right">Ingreso</th><th class="px-2 text-right">Utilidad</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($filas as $r)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2"><a href="{{ route('ganado.ficha', $r['ganado']->GanadoID) }}" class="font-mono font-semibold text-slate-800 hover:text-emerald-700">{{ $r['ganado']->areteID }}</a><span class="block text-[10px] text-slate-400">{{ $r['ganado']->categoria }} · {{ $r['ganado']->status }}</span></td>
                                <td class="px-2 text-right font-mono">{{ $r['dias'] }}</td>
                                <td class="px-2 text-right font-mono">{{ number_format($r['compra'], 0) }}</td>
                                <td class="px-2 text-right font-mono">{{ number_format($r['alimento'], 0) }}</td>
                                <td class="px-2 text-right font-mono">{{ number_format($r['medicina'], 0) }}</td>
                                <td class="px-2 text-right font-mono">{{ number_format($r['gasto_corral'] + $r['gasto_general'], 0) }}</td>
                                <td class="px-2 text-right font-mono font-semibold">{{ number_format($r['costo_total'], 0) }}</td>
                                <td class="px-2 text-right font-mono">{{ number_format($r['kg_ganados'], 1) }}</td>
                                <td class="px-2 text-right font-mono">{{ $r['costo_kg'] !== null ? number_format($r['costo_kg'], 2) : '—' }}</td>
                                <td class="px-2 text-right font-mono">{{ $r['ingreso'] !== null ? number_format($r['ingreso'], 0) : '—' }}</td>
                                <td class="px-2 text-right font-mono font-bold {{ $r['utilidad'] === null ? 'text-slate-400' : ($r['utilidad'] >= 0 ? 'text-emerald-600' : 'text-rose-600') }}">{{ $r['utilidad'] !== null ? number_format($r['utilidad'], 0) : 'Sin precio' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-10 text-center text-slate-400">Sin animales con ese filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
