@php $r = $l['racion']; @endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header :titulo="'Plan del corral ' . $corral->codigo" :subtitulo="$corral->nombre . ' · ' . $corral->fase_engorda" seccion="Plan de alimentación" icono="fa-warehouse">
            <x-slot name="acciones"><x-ui.btn :href="route('nutricion.planeacion')" variante="secundario" icono="fa-arrow-left">Todos los corrales</x-ui.btn></x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <x-ui.kpi label="Cabezas" :valor="$l['cabezas']" icono="fa-cow" />
            <x-ui.kpi label="Kilos de ganado" :valor="number_format($l['biomasa'], 0) . ' kg'" icono="fa-weight-scale" color="bg-blue-50 text-blue-600" />
            <x-ui.kpi label="Peso promedio" :valor="number_format($l['peso_prom'], 1) . ' kg'" :nota="$l['estimados'] ? $l['estimados'] . ' estimados · ' . $l['sin_pesaje'] . ' sin pesaje' : 'Pesos al día'" icono="fa-scale-balanced" color="bg-slate-100 text-slate-600" />
            <x-ui.kpi label="Alimento por día" :valor="number_format($l['kg_dia'], 0) . ' kg'" :nota="$l['kg_cab_tco'] ? number_format($l['kg_cab_tco'], 2) . ' kg por cabeza' : ''" icono="fa-wheat-awn" color="bg-amber-50 text-amber-600" />
            <x-ui.kpi label="Costo por día" :valor="formatoPesos($l['costo_dia'])" :nota="$l['cabezas'] ? formatoPesos($l['costo_dia'] / $l['cabezas']) . ' por cabeza' : ''" icono="fa-sack-dollar" color="bg-rose-50 text-rose-600" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- 1. Dieta -->
            <x-ui.card titulo="1 · Dieta del corral">
                @if ($r)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs text-emerald-700">Vigente desde {{ $l['asignacion']->desde->format('d/m/Y') }}</p>
                        <p class="text-base font-bold text-slate-900">{{ $r->codigo }} · {{ $r->nombre }}</p>
                        <p class="mt-1 text-xs text-slate-500">MS {{ $l['ms_dieta'] ? $l['ms_dieta'] . ' %' : 'sin dato' }} · {{ formatoPesos($r->costo_por_kg) }}/kg · rango {{ $r->pct_pv_min }}–{{ $r->pct_pv_max }} % PV</p>
                        <a href="{{ route('raciones.formula', $r) }}" class="mt-2 inline-block text-xs font-semibold text-emerald-700 hover:underline"><i class="fa-solid fa-flask"></i> Ver receta / parámetros</a>
                        @unless ($l['receta_ok'])<p class="mt-2 text-xs font-semibold text-amber-700"><i class="fa-solid fa-triangle-exclamation"></i> La receta no suma 100 %: no se puede explotar ni descontar de bodega.</p>@endunless
                    </div>
                @else
                    <p class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">Este corral aún no tiene dieta asignada.</p>
                @endif

                <form method="POST" action="{{ route('nutricion.plan.asignar', $corral) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <div class="flex items-end justify-between">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">{{ $r ? 'Cambiar a la dieta' : 'Asignar dieta' }}</label>
                            <a href="{{ route('nutricion.index', ['volver' => route('nutricion.plan.corral', $corral)]) }}" class="text-[11px] font-semibold text-emerald-700 hover:underline">+ Crear dieta</a>
                        </div>
                        <select name="racion_id" required class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
                            @foreach ($raciones as $op)<option value="{{ $op->id }}" @selected($r?->id === $op->id)>{{ $op->codigo }} · {{ $op->nombre }}</option>@endforeach
                        </select>
                    </div>
                    <x-ui.field label="A partir de" name="desde" type="date" :value="date('Y-m-d')" required />
                    <x-ui.btn icono="fa-check" class="w-full">{{ $r ? 'Cambiar dieta' : 'Asignar' }}</x-ui.btn>
                </form>

                @if ($corral->asignacionesDieta->count() > 0)
                    <div class="mt-4 border-t border-slate-100 pt-3">
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Historial</p>
                        @foreach ($corral->asignacionesDieta->take(6) as $a)
                            <p class="text-xs text-slate-600">{{ $a->desde->format('d/m/Y') }} → {{ $a->hasta?->format('d/m/Y') ?? 'hoy' }} · <strong>{{ $a->racion->codigo ?? '' }}</strong>{{ $a->pct_pv ? ' · ' . $a->pct_pv . ' %' : '' }}</p>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            <!-- 2. Consumo -->
            <x-ui.card titulo="2 · Consumo por % de peso vivo" class="lg:col-span-2">
                @if ($r)
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-3">
                            <form method="GET" class="flex items-end gap-2">
                                <x-ui.field label="% de peso vivo" name="pct" type="number" step="0.05" min="0.5" max="5" :value="$l['pct']" />
                                <x-ui.field label="Días" name="dias" type="number" :value="$dias" min="1" max="120" class="w-24" />
                                <x-ui.btn variante="secundario" icono="fa-calculator">Probar</x-ui.btn>
                            </form>
                            <p class="text-xs text-slate-500">Sugerido: <strong>{{ $l['pct_sugerido'] }} %</strong> {{ $r->base_consumo === 'MS' ? 'de materia seca' : 'tal como se ofrece' }} · rango de la dieta {{ $r->pct_pv_min }}–{{ $r->pct_pv_max }} %.</p>
                            @if ($l['fuera_rango'])<p class="text-xs font-semibold text-rose-600"><i class="fa-solid fa-triangle-exclamation"></i> Fuera del rango de la dieta.</p>@endif

                            <form method="POST" action="{{ route('nutricion.plan.pct', $corral) }}" class="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                @csrf @method('PUT')
                                <input type="hidden" name="pct_pv" value="{{ $l['pct'] }}">
                                @if ($l['fuera_rango'])<x-ui.field label="Motivo (fuera de rango)" name="justificacion" required />@endif
                                <div class="flex flex-wrap gap-2">
                                    <x-ui.btn icono="fa-floppy-disk">Usar {{ $l['pct'] }} % en este corral</x-ui.btn>
                                </div>
                            </form>
                            @if ($l['asignacion']?->pct_pv)
                                <form method="POST" action="{{ route('nutricion.plan.pct', $corral) }}">
                                    @csrf @method('PUT')
                                    <button class="text-xs font-semibold text-slate-500 hover:text-slate-800">↺ Volver al sugerido de la dieta ({{ $r->pct_pv_sugerido }} %)</button>
                                </form>
                            @endif
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Por cabeza / día</span><strong class="font-mono">{{ number_format($l['kg_cab_tco'], 2) }} kg</strong></div>
                            @if ($r->base_consumo === 'MS')<div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">… en materia seca</span><span class="font-mono">{{ number_format($l['kg_cab_ms'], 2) }} kg</span></div>@endif
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Corral / día</span><strong class="font-mono">{{ number_format($l['kg_dia'], 0) }} kg</strong></div>
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Periodo ({{ $dias }} días)</span><strong class="font-mono">{{ number_format($l['kg_periodo'], 0) }} kg</strong></div>
                            <div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2"><span class="text-slate-500">Servido real (7 días) por cabeza</span><span class="font-mono">{{ $l['real_cab_dia'] ? number_format($l['real_cab_dia'], 2) . ' kg' : 'sin registros' }}</span></div>
                            @can('ganaderia.nutricion')
                                <a href="{{ route('nutricion.index', ['servir' => $corral->id, 'volver' => route('nutricion.plan.corral', $corral)]) }}" class="mt-2 flex items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                                    <i class="fa-solid fa-bowl-food"></i> Registrar servido de hoy ({{ number_format($l['kg_dia'], 0) }} kg)
                                </a>
                            @endcan
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Asigna una dieta para calcular el consumo.</p>
                @endif
            </x-ui.card>
        </div>

        @include('ganaderia.plan._bodega', ['bodega' => $plan['bodega'], 'dias' => $dias])

        <!-- 3. Por animal -->
        <x-ui.card titulo="3 · Consumo por animal" :subtitulo="'Peso del último pesaje; si tiene más de ' . \App\Services\Ganaderia\BiomasaService::DIAS_VIGENCIA . ' días se proyecta con su ganancia diaria (estimado).'" :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Arete</th><th class="px-3">Categoría</th><th class="px-3">Último pesaje</th><th class="px-3 text-right">Peso usado</th>
                        @if ($r?->base_consumo === 'MS')<th class="px-3 text-right">kg MS/día</th>@endif<th class="px-3 text-right">kg/día</th><th class="px-3 text-right">kg periodo</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($animales as $a)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2 font-mono font-semibold"><a href="{{ route('ganado.ficha', $a['id']) }}" class="hover:text-emerald-700">{{ $a['arete'] }}</a></td>
                                <td class="px-3 text-xs text-slate-500">{{ $a['categoria'] }}</td>
                                <td class="px-3 text-xs {{ $a['vencido'] ? 'text-amber-600 font-semibold' : 'text-slate-500' }}">
                                    {{ $a['sin_pesaje'] ? 'Sin pesaje (peso de ingreso)' : \Carbon\Carbon::parse($a['fecha_pesaje'])->format('d/m/Y') . ' · hace ' . $a['dias_pesaje'] . ' días' }}
                                </td>
                                <td class="px-3 text-right font-mono">{{ number_format($a['peso'], 0) }}
                                    @if ($a['estimado'])<span class="ml-1 rounded bg-amber-100 px-1 text-[10px] font-bold text-amber-700" title="Registrado {{ $a['peso_registrado'] }} kg + {{ $a['gdp'] }} kg/día × {{ $a['dias_pesaje'] }} días">EST</span>@endif</td>
                                @if ($r?->base_consumo === 'MS')<td class="px-3 text-right font-mono text-slate-500">{{ $a['ms'] !== null ? number_format($a['ms'], 2) : '—' }}</td>@endif
                                <td class="px-3 text-right font-mono font-semibold">{{ $a['tco'] !== null ? number_format($a['tco'], 2) : '—' }}</td>
                                <td class="px-3 text-right font-mono text-slate-500">{{ $a['tco'] !== null ? number_format($a['tco'] * $dias, 1) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">El corral no tiene animales activos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
