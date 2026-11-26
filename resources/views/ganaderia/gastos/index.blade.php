<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ nuevo: {{ $errors->any() ? 'true' : 'false' }} }">
        <x-ui.header titulo="Gastos operativos" subtitulo="Mano de obra, combustible, luz, agua, mantenimiento, renta y demás gastos que forman el costo real por kg." icono="fa-receipt">
            <x-slot name="acciones">
                <x-ui.btn :href="request()->fullUrlWithQuery(['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Exportar Excel</x-ui.btn>
                <x-ui.btn type="button" x-on:click="nuevo = !nuevo" icono="fa-plus">Registrar gasto</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <x-ui.card titulo="Nuevo gasto" x-show="nuevo" x-cloak>
            <form method="POST" action="{{ route('gastos.store') }}" class="space-y-4">
                @csrf
                @include('ganaderia.gastos._form', ['gasto' => new \App\Models\Ganaderia\GastoOperativo()])
                <x-ui.btn icono="fa-floppy-disk">Guardar gasto</x-ui.btn>
            </form>
        </x-ui.card>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.kpi label="Total del periodo" :valor="formatoPesos($total)" icono="fa-sack-dollar" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi label="Gastos generales" :valor="formatoPesos($generales)" nota="Sin corral · se reparten entre todo el ganado" icono="fa-layer-group" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Asignados a corrales" :valor="formatoPesos($total - $generales)" icono="fa-warehouse" color="bg-indigo-50 text-indigo-600" />
        </div>

        <div class="grid gap-6 lg:grid-cols-4">
            <x-ui.card titulo="Por categoría" class="lg:col-span-1">
                <div class="space-y-2.5">
                    @forelse ($porCategoria as $cat => $monto)
                        @php $pct = $total > 0 ? round($monto / $total * 100) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-xs"><span class="text-slate-600">{{ $cat }}</span><span class="font-mono font-semibold text-slate-700">{{ formatoPesosCorto($monto) }}</span></div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-rose-500" style="width: {{ $pct }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Sin gastos en el periodo.</p>
                    @endforelse
                </div>
            </x-ui.card>

            <div class="space-y-4 lg:col-span-3">
                <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <x-ui.field label="Desde" name="desde" type="date" :value="$desde->format('Y-m-d')" />
                    <x-ui.field label="Hasta" name="hasta" type="date" :value="$hasta->format('Y-m-d')" />
                    <x-ui.field label="Categoría" name="categoria" type="select" :options="array_combine(\App\Models\Ganaderia\GastoOperativo::CATEGORIAS, \App\Models\Ganaderia\GastoOperativo::CATEGORIAS)" :value="request('categoria')" placeholder="Todas" />
                    <x-ui.field label="Corral" name="corral" type="select" :options="['general' => 'Solo generales'] + $corrales->all()" :value="request('corral')" placeholder="Todos" />
                    <label class="flex items-center gap-2 pb-2 text-xs text-slate-600"><input type="checkbox" name="anulados" value="1" @checked(request('anulados')) class="rounded border-slate-300"> Ver anulados</label>
                    <x-ui.btn variante="oscuro" icono="fa-filter">Filtrar</x-ui.btn>
                </form>

                <x-ui.card :padding="false">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="px-4 py-2.5">Fecha</th><th class="px-3">Categoría</th><th class="px-3">Concepto</th><th class="px-3">Corral</th><th class="px-3 text-right">Monto</th><th></th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($gastos as $g)
                                    <tr class="hover:bg-slate-50 {{ $g->estado !== 'vigente' ? 'opacity-50 line-through' : '' }}">
                                        <td class="px-4 py-2.5 text-xs text-slate-500">{{ $g->fecha->format('d/m/Y') }}</td>
                                        <td class="px-3 text-xs"><span class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">{{ $g->categoria }}</span></td>
                                        <td class="px-3"><p class="font-semibold text-slate-800">{{ $g->concepto }}</p><p class="text-[11px] text-slate-400">{{ $g->proveedor }}{{ $g->folio_cfdi ? ' · CFDI ' . $g->folio_cfdi : '' }}</p></td>
                                        <td class="px-3 text-xs text-slate-500">{{ $g->corral->codigo ?? 'General' }}</td>
                                        <td class="px-3 text-right font-mono font-semibold text-slate-800">{{ formatoPesos($g->monto) }}</td>
                                        <td class="px-3 text-right"><a href="{{ route('gastos.edit', $g) }}" class="text-slate-400 hover:text-slate-800"><i class="fa-solid fa-pen-to-square"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">No hay gastos con esos filtros.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($gastos->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $gastos->links() }}</div>@endif
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
