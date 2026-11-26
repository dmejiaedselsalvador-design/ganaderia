@php
    $verFinanzas = auth()->user()->can('ganaderia.finanzas');
    $card = 'rounded-2xl border border-slate-100 bg-white shadow-sm';
    $delta = function ($v) {
        if ($v === null) return ['—', 'text-slate-400', 'fa-minus'];
        return $v >= 0 ? ['+' . $v . '%', 'text-emerald-600', 'fa-arrow-trend-up'] : [$v . '%', 'text-rose-600', 'fa-arrow-trend-down'];
    };
@endphp
<x-app-layout>
    <div id="vista-dashboard" class="mx-auto max-w-7xl space-y-6">

        <!-- ENCABEZADO -->
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Planta Ganadería</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Dashboard de Análisis</h1>
                <p class="mt-1 text-sm text-slate-500">Ganado, pesos, salud y números del rancho · actualizado {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500">Período:</span>
                <div class="flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold shadow-sm">
                    @foreach ($periodos as $p)
                        <a href="{{ route('welcome', ['meses' => $p]) }}"
                           class="rounded-lg px-3 py-1.5 transition {{ $meses === $p ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900' }}">{{ $p }} meses</a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ALERTAS -->
        @if ($listosVenta > 0 || $animalesEnRetiroCount > 0)
            <div class="grid gap-4 {{ $listosVenta > 0 && $animalesEnRetiroCount > 0 ? 'md:grid-cols-2' : '' }}">
                @if ($listosVenta > 0)
                    <a href="{{ route('compras.ganado.index', ['filtro' => 'listos_venta']) }}"
                       class="group flex items-center gap-4 rounded-2xl bg-gradient-to-r from-amber-500 to-amber-600 p-4 text-white shadow-sm transition hover:shadow-md">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/20 text-lg"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="flex-1">
                            <strong class="block text-sm">{{ $listosVenta }} {{ $listosVenta === 1 ? 'cabeza alcanzó' : 'cabezas alcanzaron' }} su peso objetivo</strong>
                            <span class="text-xs text-amber-100">Listas para comercialización según su categoría.</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-sm transition group-hover:translate-x-1"></i>
                    </a>
                @endif
                @if ($animalesEnRetiroCount > 0)
                    <a href="{{ route('sanidad.index', ['filtro' => 'en_retiro']) }}"
                       class="group flex items-center gap-4 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 p-4 text-white shadow-sm transition hover:shadow-md">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/20 text-lg"><i class="fa-solid fa-ban"></i></div>
                        <div class="flex-1">
                            <strong class="block text-sm">{{ $animalesEnRetiroCount }} {{ $animalesEnRetiroCount === 1 ? 'animal' : 'animales' }} en periodo de retiro</strong>
                            <span class="text-xs text-rose-100">No enviar a rastro hasta cumplir el tiempo de retiro del fármaco.</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-sm transition group-hover:translate-x-1"></i>
                    </a>
                @endif
            </div>
        @endif

        <!-- PENDIENTES DE GESTIÓN -->
        @php $alertasVisibles = collect($alertasGestion)->filter(fn ($a) => auth()->user()->can($a[4])); @endphp
        @if ($alertasVisibles->isNotEmpty())
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($alertasVisibles as [$color, $icono, $texto, $ruta])
                    <a href="{{ route($ruta) }}" class="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold transition hover:shadow-sm
                        {{ $color === 'rose' ? 'border-rose-200 bg-rose-50 text-rose-800' : ($color === 'blue' ? 'border-blue-200 bg-blue-50 text-blue-800' : 'border-amber-200 bg-amber-50 text-amber-800') }}">
                        <i class="fa-solid {{ $icono }}"></i><span class="flex-1">{{ $texto }}</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- KPIs FINANCIEROS -->
        @if ($verFinanzas)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @if ($hayPrecios)
                    @php $util = $rentabilidad['utilidad_proyectada']; @endphp
                    <div class="{{ $card }} p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valor de mercado del ganado</p>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-cow"></i></span>
                        </div>
                        <p class="mt-2 text-2xl font-black text-slate-900" title="{{ formatoPesos($rentabilidad['valor_mercado_activos']) }}">{{ formatoPesosCorto($rentabilidad['valor_mercado_activos']) }}</p>
                        <p class="mt-1 flex items-center gap-1.5 text-xs">
                            <span class="font-semibold {{ $util >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $util >= 0 ? '+' : '' }}{{ formatoPesosCorto($util) }}</span>
                            <span class="text-slate-400">utilidad proyectada vs costo acumulado</span>
                        </p>
                    </div>
                @else
                    @php [$dTxt, $dCls, $dIco] = $delta($plusvaliaPct); @endphp
                    <div class="{{ $card }} p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valor del ganado (a precio de compra)</p>
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-cow"></i></span>
                        </div>
                        <p class="mt-2 text-2xl font-black text-slate-900">{{ formatoPesosCorto($valorHato) }}</p>
                        <p class="mt-1 text-xs text-amber-600">
                            @can('ganaderia.configuracion')<a href="{{ route('configuracion.index') }}" class="underline">Registra precios de mercado</a>@else Faltan precios de mercado @endcan para ver el valor real.
                        </p>
                    </div>
                @endif

                @php [$dTxt, $dCls, $dIco] = $delta($ingresosVariacionPct); @endphp
                <div class="{{ $card }} p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ventas · {{ $meses }} meses</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-slate-900" title="{{ formatoPesos($ingresosPeriodo) }}">{{ formatoPesosCorto($ingresosPeriodo) }}</p>
                    <p class="mt-1 flex items-center gap-1.5 text-xs">
                        <span class="font-semibold {{ $dCls }}"><i class="fa-solid {{ $dIco }}"></i> {{ $dTxt }}</span>
                        <span class="text-slate-400">vs período anterior</span>
                    </p>
                </div>

                <div class="{{ $card }} p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Costo operativo · {{ $meses }} meses</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-wheat-awn"></i></span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-slate-900" title="{{ formatoPesos($alimentoPeriodo + $sanidadPeriodo) }}">{{ formatoPesosCorto($alimentoPeriodo + $sanidadPeriodo) }}</p>
                    <p class="mt-1 text-xs text-slate-400">
                        Alimento {{ formatoPesosCorto($alimentoPeriodo) }} · Sanidad {{ formatoPesosCorto($sanidadPeriodo) }}
                    </p>
                </div>

                <div class="{{ $card }} p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Costo por kg producido</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid fa-scale-balanced"></i></span>
                    </div>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $costoPorKg !== null ? '$ ' . number_format($costoPorKg, 2) : '—' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ number_format($kgProducidos, 0) }} kg ganados en el período</p>
                </div>
            </div>
        @endif

        <!-- KPIs ZOOTÉCNICOS -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            @foreach ([
                ['Cabezas activas', number_format($ganados), 'cab.', 'fa-cow', 'text-blue-600', number_format($kgEnPie, 0) . ' kg en pie'],
                ['Peso promedio', number_format($pesoGanados, 1), 'kg', 'fa-weight-scale', 'text-slate-700', $ganadoExportar . ' sobre 200 kg'],
                ['GDP promedio', number_format($gdpPromedio, 2), 'kg/día', 'fa-chart-line', $gdpPromedio >= 1.2 ? 'text-emerald-600' : 'text-amber-600', 'Ganancia diaria de peso'],
                ['Ocupación corrales', $ocupacionPct, '%', 'fa-warehouse', $ocupacionPct > 95 ? 'text-rose-600' : 'text-indigo-600', $ocupados . ' / ' . $capacidadTotal . ' espacios'],
                ['Mortalidad', $mortalidadPct, '%', 'fa-skull', $mortalidadPct > 2 ? 'text-rose-600' : 'text-emerald-600', $totalMuertos . ' bajas · ' . $totalVendidos . ' vendidos'],
                ['Rend. canal', $rendimientoPromedioCanal ?? '—', $rendimientoPromedioCanal ? '%' : '', 'fa-drumstick-bite', 'text-rose-600', $canalesEvaluadasCount . ' canales evaluadas'],
            ] as [$label, $valor, $unidad, $icono, $color, $nota])
                <div class="{{ $card }} p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</span>
                        <i class="fa-solid {{ $icono }} text-xs text-slate-300"></i>
                    </div>
                    <div class="mt-1 text-xl font-black {{ $color }}">{{ $valor }} <span class="text-xs font-medium text-slate-400">{{ $unidad }}</span></div>
                    <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ $nota }}</p>
                </div>
            @endforeach
        </div>

        <!-- GRÁFICAS PRINCIPALES -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="{{ $card }} p-6 lg:col-span-2">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Evolución productiva</h3>
                        <p class="text-xs text-slate-400">Peso promedio en báscula y GDP mensual</p>
                    </div>
                </div>
                <div class="relative h-72"><canvas id="chartProduccion"></canvas></div>
            </div>

            <div class="{{ $card }} p-6">
                <h3 class="text-base font-bold text-slate-800">Ganado por categoría</h3>
                <p class="mb-4 text-xs text-slate-400">Cabezas activas por categoría</p>
                <div class="relative h-56"><canvas id="chartCategoria"></canvas></div>
                <div class="mt-4 flex justify-center gap-4 text-xs">
                    @foreach ($porSexo as $s)
                        <span class="flex items-center gap-1.5 text-slate-600">
                            <i class="fa-solid {{ $s->sexo === 'Hembra' ? 'fa-venus text-pink-500' : 'fa-mars text-blue-500' }}"></i>
                            {{ $s->sexo ?? 'N/D' }}: <strong class="text-slate-800">{{ $s->total }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @if ($verFinanzas)
                <div class="{{ $card }} p-6 lg:col-span-2">
                    <h3 class="text-base font-bold text-slate-800">Flujo financiero mensual</h3>
                    <p class="mb-4 text-xs text-slate-400">Ventas vs compras de ganado vs costo de alimentación (MXN)</p>
                    <div class="relative h-72"><canvas id="chartFinanzas"></canvas></div>
                </div>
            @endif

            <div class="{{ $card }} p-6 {{ $verFinanzas ? '' : 'lg:col-span-3' }}">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Ocupación de corrales</h3>
                        <p class="text-xs text-slate-400">Cabezas activas vs capacidad</p>
                    </div>
                    @can('ganaderia.corrales')
                        <a href="{{ route('corrales.index') }}" class="text-xs font-semibold text-emerald-700 hover:underline">Ver todos</a>
                    @endcan
                </div>
                <div class="space-y-4">
                    @forelse ($corrales as $c)
                        @php $pct = $c->capacidad_maxima > 0 ? min(100, round($c->ganados_activos_count / $c->capacidad_maxima * 100)) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700">{{ $c->codigo }} <span class="font-normal text-slate-400">· {{ $c->fase_engorda }}</span></span>
                                <span class="font-mono text-slate-500">{{ $c->ganados_activos_count }}/{{ $c->capacidad_maxima }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $pct >= 95 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-slate-400">No hay corrales activos.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TABLAS DE ANÁLISIS -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="{{ $card }} overflow-hidden lg:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Próximos a peso de venta</h3>
                        <p class="text-xs text-slate-400">Animales con mayor avance hacia su peso objetivo</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="px-6 py-2.5">Arete</th>
                                <th class="px-3 py-2.5">Categoría</th>
                                <th class="px-3 py-2.5">Corral</th>
                                <th class="px-3 py-2.5 text-right">Peso / Meta</th>
                                <th class="w-40 px-6 py-2.5">Avance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($proximosVenta as $g)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-2.5 font-mono text-xs font-semibold text-slate-800">
                                        @can('ganaderia.ganado')
                                            <a href="{{ route('compras.ganado.perfil', ['arete' => $g->areteID]) }}" class="hover:text-emerald-700">{{ $g->areteID }}</a>
                                        @else
                                            {{ $g->areteID }}
                                        @endcan
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-slate-600">{{ $g->categoria }}</td>
                                    <td class="px-3 py-2.5 text-xs text-slate-500">{{ $g->corralLote->codigo ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-right font-mono text-xs text-slate-700">{{ number_format($g->ultimoPeso, 0) }} / {{ number_format($g->objetivo, 0) }} kg</td>
                                    <td class="px-6 py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full {{ $g->avance_pct >= 100 ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ $g->avance_pct }}%"></div>
                                            </div>
                                            <span class="w-10 text-right font-mono text-[11px] font-semibold {{ $g->avance_pct >= 100 ? 'text-amber-600' : 'text-slate-500' }}">{{ round($g->avance_pct) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">Sin animales activos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <div class="{{ $card }} p-6">
                    <h3 class="text-base font-bold text-slate-800">Mejor GDP del período</h3>
                    <p class="mb-3 text-xs text-slate-400">Pesajes con mayor ganancia diaria</p>
                    <ol class="space-y-2.5">
                        @forelse ($topGdp as $i => $p)
                            <li class="flex items-center gap-3 text-sm">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $i === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">{{ $i + 1 }}</span>
                                <span class="flex-1 truncate font-mono text-xs text-slate-700">{{ $p->ganado->areteID ?? '—' }} <span class="font-sans text-slate-400">· {{ $p->ganado->categoria ?? '' }}</span></span>
                                <span class="font-mono text-xs font-bold text-emerald-600">{{ number_format($p->gdp, 2) }} kg/d</span>
                            </li>
                        @empty
                            <li class="py-4 text-center text-sm text-slate-400">Sin pesajes en el período.</li>
                        @endforelse
                    </ol>
                </div>

                <div class="{{ $card }} p-6">
                    <h3 class="text-base font-bold text-slate-800">Bajas por causa</h3>
                    <p class="mb-3 text-xs text-slate-400">Histórico de mortalidad</p>
                    @forelse ($bajasPorCausa as $b)
                        @php $pct = $totalMuertos ? round($b->total / $totalMuertos * 100) : 0; @endphp
                        <div class="mb-2.5">
                            <div class="flex justify-between text-xs"><span class="text-slate-600">{{ $b->causa_muerte }}</span><span class="font-semibold text-slate-700">{{ $b->total }}</span></div>
                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-rose-500" style="width: {{ $pct }}%"></div></div>
                        </div>
                    @empty
                        <p class="flex items-center gap-2 py-3 text-sm text-emerald-600"><i class="fa-solid fa-circle-check"></i> Sin bajas registradas.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ACCESOS RÁPIDOS -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([
                ['ganaderia.ganado', 'compras.ganado.index', 'fa-cow', 'Inventario', 'bg-blue-50 text-blue-700'],
                ['ganaderia.corrales', 'corrales.index', 'fa-warehouse', 'Corrales', 'bg-amber-50 text-amber-700'],
                ['ganaderia.nutricion', 'nutricion.index', 'fa-wheat-awn', 'Nutrición', 'bg-emerald-50 text-emerald-700'],
                ['ganaderia.sanidad', 'sanidad.index', 'fa-notes-medical', 'Sanidad', 'bg-rose-50 text-rose-700'],
                ['ganaderia.ventas', 'ventas.index', 'fa-cart-shopping', 'Ventas', 'bg-teal-50 text-teal-700'],
                ['ganaderia.simulador', 'simulador.index', 'fa-calculator', 'Simulador', 'bg-indigo-50 text-indigo-700'],
            ] as [$permiso, $ruta, $icono, $texto, $color])
                @can($permiso)
                    <a href="{{ route($ruta) }}" class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 text-xs font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $color }}"><i class="fa-solid {{ $icono }}"></i></span>
                        {{ $texto }}
                    </a>
                @endcan
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const t = window.temaChart();
            const meses = @json($labelsMeses);
            const pesos = v => '$ ' + Number(v).toLocaleString('es-MX', { maximumFractionDigits: 0 });
            const escalas = (extra = {}) => ({
                x: { grid: { display: false }, ticks: { color: t.texto } },
                y: { grid: { color: t.grid }, ticks: { color: t.texto }, beginAtZero: true, ...extra },
            });

            // 1. Evolución productiva: peso promedio (línea) + GDP (barras)
            new Chart(document.getElementById('chartProduccion'), {
                data: {
                    labels: meses,
                    datasets: [
                        { type: 'line', label: 'Peso promedio (kg)', data: @json($pesoMensual), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)',
                          fill: true, tension: .35, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#10b981', yAxisID: 'y', spanGaps: true },
                        { type: 'bar', label: 'GDP (kg/día)', data: @json($gdpMensual), backgroundColor: 'rgba(245,158,11,.75)', borderRadius: 6, maxBarThickness: 28, yAxisID: 'y1' },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', labels: { color: t.texto, boxWidth: 12, usePointStyle: true } } },
                    scales: {
                        ...escalas(),
                        y1: { position: 'right', beginAtZero: true, grid: { display: false }, ticks: { color: t.texto } },
                    },
                },
            });

            // 2. Composición por categoría
            const cat = @json($porCategoria);
            new Chart(document.getElementById('chartCategoria'), {
                type: 'doughnut',
                data: {
                    labels: cat.map(c => c.categoria || 'N/D'),
                    datasets: [{ data: cat.map(c => c.total), backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6'], borderColor: t.superficie, borderWidth: 3 }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '68%',
                    plugins: { legend: { position: 'right', labels: { color: t.texto, boxWidth: 10, usePointStyle: true, font: { size: 11 } } } },
                },
            });

            // 3. Flujo financiero
            const el = document.getElementById('chartFinanzas');
            if (el) {
                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: meses,
                        datasets: [
                            { label: 'Ventas', data: @json($ventasMensual), backgroundColor: '#10b981', borderRadius: 5, maxBarThickness: 22 },
                            { label: 'Compras de ganado', data: @json($comprasMensual), backgroundColor: '#3b82f6', borderRadius: 5, maxBarThickness: 22 },
                            { label: 'Alimentación', data: @json($alimentoMensual), backgroundColor: '#f59e0b', borderRadius: 5, maxBarThickness: 22 },
                        ],
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { color: t.texto, boxWidth: 12, usePointStyle: true } },
                            tooltip: { callbacks: { label: c => c.dataset.label + ': ' + pesos(c.raw) } },
                        },
                        scales: escalas({ ticks: { color: t.texto, callback: v => '$' + Number(v).toLocaleString('es-MX') } }),
                    },
                });
            }
        });
    </script>
</x-app-layout>
