@php
    $cmp = function ($real, $meta, $menorEsMejor = false) {
        if ($real === null || $meta === null || (float) $meta == 0) return 'text-slate-700';
        $ok = $menorEsMejor ? $real <= (float) $meta : $real >= (float) $meta;
        return $ok ? 'text-emerald-600' : 'text-rose-600';
    };
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Rentabilidad por lote" subtitulo="Agrupado por el corral actual (o último) de cada animal. Verde = cumple la meta, rojo = no cumple." seccion="Reportes" icono="fa-warehouse">
            <x-slot name="acciones">
                <x-ui.btn :href="route('reportes.lotes', ['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Excel</x-ui.btn>
                <x-ui.btn :href="route('reportes.lotes', ['exportar' => 'pdf'])" variante="secundario" icono="fa-file-pdf">PDF</x-ui.btn>
                @can('ganaderia.configuracion')<x-ui.btn :href="route('configuracion.index')" variante="oscuro" icono="fa-bullseye">Metas</x-ui.btn>@endcan
            </x-slot>
        </x-ui.header>

        <div class="grid gap-5 lg:grid-cols-2">
            @forelse ($lotes as $l)
                @php $c = $l['corral']; @endphp
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-slate-900">{{ $l['nombre'] }}</h3>
                            <p class="text-xs text-slate-500">{{ $l['cabezas'] }} cabezas ({{ $l['activos'] }} activas){{ $c?->ubicacion ? ' · ' . $c->ubicacion->nombre : '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] uppercase text-slate-400">Utilidad</p>
                            <p class="font-mono text-lg font-black {{ $l['utilidad'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ formatoPesosCorto($l['utilidad']) }}</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-2 text-center text-xs">
                        <div class="rounded-lg bg-slate-50 p-2"><p class="text-slate-400">Compra</p><p class="font-mono font-semibold">{{ formatoPesosCorto($l['compra']) }}</p></div>
                        <div class="rounded-lg bg-slate-50 p-2"><p class="text-slate-400">Alimento</p><p class="font-mono font-semibold">{{ formatoPesosCorto($l['alimento']) }}</p></div>
                        <div class="rounded-lg bg-slate-50 p-2"><p class="text-slate-400">Medicinas</p><p class="font-mono font-semibold">{{ formatoPesosCorto($l['medicina']) }}</p></div>
                        <div class="rounded-lg bg-slate-50 p-2"><p class="text-slate-400">Gastos</p><p class="font-mono font-semibold">{{ formatoPesosCorto($l['gastos']) }}</p></div>
                    </div>
                    <table class="mt-4 w-full text-xs">
                        <thead><tr class="text-left text-[10px] uppercase text-slate-400"><th class="py-1">Indicador</th><th class="text-right">Real</th><th class="text-right">Meta</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr><td class="py-1.5 text-slate-600">GDP (kg/día)</td><td class="text-right font-mono font-bold {{ $cmp($l['gdp'], $c?->meta_gdp) }}">{{ $l['gdp'] ?? '—' }}</td><td class="text-right font-mono text-slate-400">{{ $c?->meta_gdp ?? '—' }}</td></tr>
                            <tr><td class="py-1.5 text-slate-600">Costo por kg producido</td><td class="text-right font-mono font-bold {{ $cmp($l['costo_kg'], $c?->meta_costo_kg, true) }}">{{ $l['costo_kg'] !== null ? '$ ' . number_format($l['costo_kg'], 2) : '—' }}</td><td class="text-right font-mono text-slate-400">{{ $c?->meta_costo_kg ? '$ ' . number_format($c->meta_costo_kg, 2) : '—' }}</td></tr>
                            <tr><td class="py-1.5 text-slate-600">Conversión alimenticia</td><td class="text-right font-mono font-bold {{ $cmp($l['conversion'], $c?->meta_conversion, true) }}">{{ $l['conversion'] ?? '—' }}</td><td class="text-right font-mono text-slate-400">{{ $c?->meta_conversion ?? '—' }}</td></tr>
                            <tr><td class="py-1.5 text-slate-600">Mortalidad</td><td class="text-right font-mono font-bold {{ $cmp($l['mortalidad_pct'], $c?->meta_mortalidad_pct, true) }}">{{ $l['mortalidad_pct'] }}%</td><td class="text-right font-mono text-slate-400">{{ $c?->meta_mortalidad_pct !== null ? $c->meta_mortalidad_pct . '%' : '—' }}</td></tr>
                            <tr><td class="py-1.5 text-slate-600">Gasto real vs presupuesto</td><td class="text-right font-mono font-bold {{ $cmp($l['gasto_real'], $c?->presupuesto_mxn, true) }}">{{ formatoPesosCorto($l['gasto_real']) }}</td><td class="text-right font-mono text-slate-400">{{ $c?->presupuesto_mxn ? formatoPesosCorto($c->presupuesto_mxn) : '—' }}</td></tr>
                        </tbody>
                    </table>
                    @if ($l['sin_precio'])<p class="mt-2 text-[11px] text-amber-600"><i class="fa-solid fa-triangle-exclamation"></i> {{ $l['sin_precio'] }} animales sin precio de mercado: su utilidad no se incluye.</p>@endif
                </div>
            @empty
                <p class="text-sm text-slate-400">Sin datos.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
