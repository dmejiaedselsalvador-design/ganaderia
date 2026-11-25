<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Rentabilidad y reportes" subtitulo="Utilidad real por animal y por lote, y reportes para descargar en Excel o PDF." icono="fa-chart-column" />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-ui.kpi label="Valor de mercado del hato" :valor="formatoPesos($resumen['valor_mercado_activos'])" :nota="$resumen['activos_sin_precio'] ? $resumen['activos_sin_precio'] . ' animales sin precio de su categoría' : 'Peso actual x precio vigente'" icono="fa-scale-balanced" />
            <x-ui.kpi label="Costo acumulado del hato" :valor="formatoPesos($resumen['costo_activos'])" nota="Compra + alimento + medicinas + gastos" icono="fa-sack-dollar" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi label="Utilidad proyectada" :valor="formatoPesos($resumen['utilidad_proyectada'])" :valorClass="$resumen['utilidad_proyectada'] >= 0 ? 'text-emerald-600' : 'text-rose-600'" nota="Si se vendiera hoy a precio de mercado" icono="fa-chart-line" />
            <x-ui.kpi label="Utilidad realizada" :valor="formatoPesos($resumen['utilidad_realizada'])" :valorClass="$resumen['utilidad_realizada'] >= 0 ? 'text-emerald-600' : 'text-rose-600'" :nota="$resumen['vendidos'] . ' animales vendidos' . ($resumen['margen_realizado_pct'] !== null ? ' · margen ' . $resumen['margen_realizado_pct'] . '%' : '')" icono="fa-hand-holding-dollar" color="bg-teal-50 text-teal-600" />
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['Rentabilidad por lote', 'Costos, utilidad, GDP, conversión y mortalidad de cada corral contra sus metas.', 'fa-warehouse', route('reportes.lotes'), [['Excel', route('reportes.lotes', ['exportar' => 'csv'])], ['PDF', route('reportes.lotes', ['exportar' => 'pdf'])]]],
                ['Rentabilidad por animal', 'Desglose de costos y utilidad de cada animal; identifica los que pierden dinero.', 'fa-cow', route('reportes.animales'), [['Excel', route('reportes.animales', ['exportar' => 'csv', 'estado' => 'todos'])]]],
                ['Inventario de ganado por corral', 'Listado de animales activos con SINIIGA, pesos y días en engorda.', 'fa-list-ol', null, [['Excel', route('reportes.inventario')], ['PDF', route('reportes.inventario', ['exportar' => 'pdf'])]]],
                ['Cuentas por cobrar', 'Saldos de clientes por antigüedad.', 'fa-hand-holding-dollar', auth()->user()->can('ganaderia.clientes') ? route('cobranza.index') : null, auth()->user()->can('ganaderia.clientes') ? [['Excel', route('cobranza.index', ['exportar' => 'csv'])]] : []],
                ['Gastos operativos', 'Gastos por periodo, categoría y corral.', 'fa-receipt', auth()->user()->can('ganaderia.gastos') ? route('gastos.index') : null, auth()->user()->can('ganaderia.gastos') ? [['Excel', route('gastos.index', ['exportar' => 'csv'])]] : []],
                ['Inventario de insumos y kardex', 'Existencias valuadas a costo promedio.', 'fa-boxes-stacked', auth()->user()->can('ganaderia.inventario') ? route('inventario.index') : null, auth()->user()->can('ganaderia.inventario') ? [['Excel', route('inventario.index', ['exportar' => 'csv'])]] : []],
            ] as [$t, $d, $i, $ver, $desc])
                <div class="flex flex-col rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid {{ $i }}"></i></div>
                    <h3 class="mt-4 font-bold text-slate-900">{{ $t }}</h3>
                    <p class="mt-1 flex-1 text-xs text-slate-500">{{ $d }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($ver)<x-ui.btn :href="$ver" variante="oscuro" icono="fa-eye">Ver</x-ui.btn>@endif
                        @foreach ($desc as [$fmt, $url])
                            <x-ui.btn :href="$url" variante="secundario" :icono="$fmt === 'PDF' ? 'fa-file-pdf' : 'fa-file-excel'">{{ $fmt }}</x-ui.btn>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
