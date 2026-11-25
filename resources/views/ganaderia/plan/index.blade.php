@php
    $lineas = collect($plan['lineas']);
    $conDieta = $lineas->filter(fn ($l) => $l['racion']);
    $faltantes = $plan['bodega']->where('estado', 'faltante');
    $coberturaMin = $plan['bodega']->whereNotNull('cobertura')->min('cobertura');
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Plan de alimentación" subtitulo="Cabezas y peso de cada corral ⇒ consumo sugerido por % de peso vivo ⇒ ingredientes de la receta ⇒ bodega." seccion="Alimentación" icono="fa-diagram-project" />
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <x-ui.kpi label="Cabezas en corrales" :valor="number_format($lineas->sum('cabezas'))" icono="fa-cow" />
            <x-ui.kpi label="Biomasa" :valor="number_format($lineas->sum('biomasa'), 0) . ' kg'" :nota="$lineas->sum('estimados') ? $lineas->sum('estimados') . ' pesos estimados (pesaje viejo)' : 'Pesos al día'" icono="fa-weight-scale" color="bg-blue-50 text-blue-600" />
            <x-ui.kpi label="Alimento por día" :valor="number_format($lineas->sum('kg_dia'), 0) . ' kg'" nota="Tal como se ofrece" icono="fa-wheat-awn" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Costo por día" :valor="formatoPesos($lineas->sum('costo_dia'))" icono="fa-sack-dollar" color="bg-rose-50 text-rose-600" />
            <x-ui.kpi label="La bodega alcanza" :valor="$coberturaMin !== null ? $coberturaMin . ' días' : '—'" :nota="$faltantes->count() ? $faltantes->count() . ' ingredientes con faltante' : 'Para el ingrediente más justo'" icono="fa-warehouse" :color="$faltantes->count() ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'" />
        </div>

        <!-- Corrales -->
        <form method="GET" class="space-y-3">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <p class="text-sm text-slate-500">Puedes ajustar el % de peso vivo de cualquier corral para ver el efecto, y guardarlo en su detalle.</p>
                <div class="flex items-end gap-2">
                    <x-ui.field label="Proyectar (días)" name="dias" type="number" :value="$dias" min="1" max="120" class="w-28" />
                    <x-ui.btn variante="oscuro" icono="fa-calculator">Recalcular</x-ui.btn>
                </div>
            </div>

            <x-ui.card :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-2.5">Corral</th><th class="px-3">Dieta</th><th class="px-3 text-right">Cabezas</th><th class="px-3 text-right">Peso prom.</th>
                            <th class="px-3 text-center">% peso vivo</th><th class="px-3 text-right">kg/cab/día</th><th class="px-3 text-right">Servido real</th>
                            <th class="px-3 text-right">kg corral/día</th><th class="px-3 text-right">Costo/día</th><th></th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($lineas as $l)
                                @php
                                    $dif = $l['real_cab_dia'] && $l['kg_cab_tco'] ? round(($l['real_cab_dia'] - $l['kg_cab_tco']) / $l['kg_cab_tco'] * 100) : null;
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3"><a href="{{ route('nutricion.plan.corral', $l['corral']) }}" class="font-semibold text-slate-800 hover:text-emerald-700">{{ $l['corral']->codigo }}</a>
                                        <span class="block text-[11px] text-slate-400">{{ $l['corral']->fase_engorda }}</span></td>
                                    <td class="px-3 text-xs">
                                        @if ($l['racion'])
                                            <span class="font-semibold text-slate-700">{{ $l['racion']->codigo }}</span>
                                            @unless ($l['receta_ok'])<span class="block text-[10px] font-semibold text-amber-600">Receta incompleta</span>@endunless
                                        @else
                                            <a href="{{ route('nutricion.plan.corral', $l['corral']) }}" class="font-semibold text-amber-600 hover:underline"><i class="fa-solid fa-plus"></i> Asignar dieta</a>
                                        @endif
                                    </td>
                                    <td class="px-3 text-right font-mono">{{ $l['cabezas'] }}</td>
                                    <td class="px-3 text-right font-mono">{{ number_format($l['peso_prom'], 0) }} kg
                                        @if ($l['estimados'])<span class="block text-[10px] font-semibold text-amber-600" title="Animales con pesaje de más de 21 días: peso proyectado con su ganancia diaria">{{ $l['estimados'] }} estimados</span>@endif
                                    </td>
                                    <td class="px-3 text-center">
                                        @if ($l['racion'])
                                            <input type="number" step="0.05" min="0.5" max="5" name="pct[{{ $l['corral']->id }}]" value="{{ $l['pct'] }}"
                                                   class="w-20 rounded-lg border-slate-200 py-1 text-center font-mono text-xs {{ $l['fuera_rango'] ? 'border-rose-300 text-rose-600' : '' }}">
                                            <span class="block text-[10px] text-slate-400">sug. {{ $l['pct_sugerido'] }} %{{ $l['racion']->base_consumo === 'MS' ? ' MS' : '' }}</span>
                                        @else — @endif
                                    </td>
                                    <td class="px-3 text-right font-mono">{{ $l['kg_cab_tco'] ? number_format($l['kg_cab_tco'], 2) : '—' }}
                                        @if ($l['kg_cab_ms'] && $l['racion']?->base_consumo === 'MS')<span class="block text-[10px] text-slate-400">{{ number_format($l['kg_cab_ms'], 2) }} MS</span>@endif</td>
                                    <td class="px-3 text-right font-mono text-xs">{{ $l['real_cab_dia'] ? number_format($l['real_cab_dia'], 2) : '—' }}
                                        @if ($dif !== null)<span class="block text-[10px] font-semibold {{ abs($dif) <= 10 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $dif > 0 ? '+' : '' }}{{ $dif }} %</span>@endif</td>
                                    <td class="px-3 text-right font-mono font-semibold">{{ number_format($l['kg_dia'], 0) }}</td>
                                    <td class="px-3 text-right font-mono text-xs">{{ formatoPesos($l['costo_dia']) }}</td>
                                    <td class="px-3 text-right"><a href="{{ route('nutricion.plan.corral', $l['corral']) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Detalle</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </form>

        @include('ganaderia.plan._bodega', ['bodega' => $plan['bodega'], 'dias' => $dias])

        <p class="text-xs text-slate-400">kg tal como se ofrece = kg de materia seca ÷ (% materia seca de la dieta). «Servido real» = promedio de los últimos 7 días por cabeza; la diferencia indica si se sirve de más o de menos.</p>
    </div>
</x-app-layout>
