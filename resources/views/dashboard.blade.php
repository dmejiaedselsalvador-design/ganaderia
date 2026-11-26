<x-app-layout>
    @php
        $pctExport = ($ganados ?? 0) > 0 ? min(100, round(($ganadoExportar / $ganados) * 100)) : 0;
        $circ = 2 * pi() * 34;
        $offset = $circ * (1 - $pctExport / 100);
    @endphp

    <style>
        @keyframes fadeUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { opacity: 0; animation: fadeUp .7s cubic-bezier(.2,.7,.2,1) forwards; }
        .d1 { animation-delay: .05s } .d2 { animation-delay: .15s } .d3 { animation-delay: .25s }
        .d4 { animation-delay: .35s } .d5 { animation-delay: .45s } .d6 { animation-delay: .55s }
        @keyframes ring { from { stroke-dashoffset: {{ $circ }}; } to { stroke-dashoffset: {{ $offset }}; } }
        .ring-anim { animation: ring 1.6s cubic-bezier(.2,.7,.2,1) .4s both; }
    </style>

    <div id="vista-dashboard" class="mx-auto max-w-7xl space-y-8 px-2 py-2 sm:px-4 lg:px-6">

        <!-- ================= HERO ================= -->
        <section class="fade-up d1 relative overflow-hidden rounded-3xl bg-[#05201a] p-8 text-white shadow-2xl shadow-emerald-950/20 sm:p-10">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-32 -left-24 h-96 w-96 rounded-full bg-emerald-500/25 blur-3xl"></div>
                <div class="absolute -bottom-40 right-0 h-96 w-96 rounded-full bg-amber-400/15 blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.06]" style="background-image: linear-gradient(to right,#fff 1px,transparent 1px),linear-gradient(to bottom,#fff 1px,transparent 1px);background-size:48px 48px;"></div>
                <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-amber-300/60 to-transparent"></div>
            </div>

            <div class="relative flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
                <div class="max-w-2xl">
                    <div class="mb-5 flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-200">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span> Sistema activo
                        </span>
                        <span class="text-xs uppercase tracking-[0.2em] text-amber-200/70">
                            {{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
                        </span>
                    </div>
                    <h1 class="font-['Playfair_Display'] text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
                        Panel de <span class="text-amber-300">Control</span> Ganadero
                    </h1>
                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-emerald-100/70 sm:text-base">
                        @auth Bienvenido, {{ auth()->user()->name }}. @endauth
                        Monitoreo en tiempo real de inventario, peso y métricas de su ganado.
                    </p>
                </div>

                <!-- Anillo de exportación -->
                <div class="flex items-center gap-5 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur">
                    <div class="relative h-24 w-24">
                        <svg class="h-24 w-24 -rotate-90" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="34" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="6"/>
                            <circle cx="40" cy="40" r="34" fill="none" stroke="url(#gold)" stroke-width="6" stroke-linecap="round"
                                    stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}" class="ring-anim"/>
                            <defs>
                                <linearGradient id="gold" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fcd34d"/><stop offset="100%" stop-color="#d97706"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center font-['Playfair_Display'] text-xl font-semibold text-amber-300">{{ $pctExport }}%</div>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.25em] text-emerald-200/50">Ganado listo</p>
                        <p class="mt-1 font-['Playfair_Display'] text-lg font-semibold">Para exportación</p>
                        <p class="text-xs text-emerald-100/50">{{ $ganadoExportar }} de {{ $ganados }} cabezas</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= KPIs ================= -->
        <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">

            @php
                $kpis = [
                    ['d' => 'd2', 'label' => 'Total de ganado', 'val' => $ganados, 'dec' => 0, 'unit' => 'Cabezas', 'icon' => 'fa-cow', 'tone' => 'emerald', 'prefix' => ''],
                    ['d' => 'd3', 'label' => 'Peso promedio', 'val' => $pesoGanados, 'dec' => 2, 'unit' => 'kg', 'icon' => 'fa-weight-scale', 'tone' => 'amber', 'prefix' => ''],
                    ['d' => 'd4', 'label' => 'Listos exportación', 'val' => $ganadoExportar, 'dec' => 0, 'unit' => 'Cabezas', 'icon' => 'fa-ship', 'tone' => 'emerald', 'prefix' => ''],
                    ['d' => 'd5', 'label' => 'Costo alimento / día', 'val' => 0, 'dec' => 2, 'unit' => 'USD', 'icon' => 'fa-wheat-awn', 'tone' => 'amber', 'prefix' => '$'],
                ];
            @endphp

            @foreach ($kpis as $k)
                <div class="fade-up {{ $k['d'] }} group relative overflow-hidden rounded-2xl border border-stone-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-900/10">
                    <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-transparent via-amber-400 to-transparent opacity-60 transition group-hover:opacity-100"></div>
                    <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full {{ $k['tone'] === 'emerald' ? 'bg-emerald-500/5' : 'bg-amber-400/10' }} transition-transform duration-500 group-hover:scale-150"></div>

                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">{{ $k['label'] }}</p>
                            <h3 class="mt-3 font-['Playfair_Display'] text-4xl font-semibold tracking-tight text-[#05201a]">
                                <span data-count="{{ $k['val'] }}" data-decimals="{{ $k['dec'] }}" data-prefix="{{ $k['prefix'] }}">{{ $k['prefix'] }}{{ number_format($k['val'], $k['dec']) }}</span>
                            </h3>
                            <p class="mt-1 text-xs font-medium text-stone-400">{{ $k['unit'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $k['tone'] === 'emerald' ? 'bg-[#05201a] text-amber-300' : 'bg-amber-50 text-amber-600 ring-1 ring-amber-200' }} shadow-md transition-transform duration-300 group-hover:rotate-6">
                            <i class="fa-solid {{ $k['icon'] }} text-lg"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <!-- ================= GRÁFICAS ================= -->
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Tendencia -->
            <div class="fade-up d4 flex flex-col rounded-2xl border border-stone-200/80 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="font-['Playfair_Display'] text-xl font-semibold text-[#05201a]">Tendencia de ganancia de peso</h3>
                        <p class="mt-0.5 text-xs text-stone-400">Evolución del peso promedio del ganado</p>
                    </div>
                    <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-amber-700">Últimos meses</span>
                </div>
                <div class="relative h-72 w-full"><canvas id="lineChart"></canvas></div>
            </div>

            <!-- Dona -->
            <div class="fade-up d5 flex flex-col rounded-2xl border border-stone-200/80 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <h3 class="font-['Playfair_Display'] text-xl font-semibold text-[#05201a]">Distribución por sexo</h3>
                    <p class="mt-0.5 text-xs text-stone-400">Ganado actual por categoría</p>
                </div>
                <div class="relative flex h-72 w-full items-center justify-center"><canvas id="doughnutChartCategoria"></canvas></div>
            </div>

            <!-- Barras -->
            <div class="fade-up d6 rounded-2xl border border-stone-200/80 bg-white p-6 shadow-sm lg:col-span-3">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="font-['Playfair_Display'] text-xl font-semibold text-[#05201a]">Inventario detallado por categoría</h3>
                        <p class="mt-0.5 text-xs text-stone-400">Animales activos en corrales</p>
                    </div>
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Activos</span>
                </div>
                <div class="relative h-72 w-full"><canvas id="doughnutChart"></canvas></div>
            </div>
        </section>
    </div>

    <!-- ================= SCRIPTS ================= -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            // --- Contadores animados ---
            document.querySelectorAll('[data-count]').forEach(el => {
                const target = parseFloat(el.dataset.count) || 0;
                const dec = parseInt(el.dataset.decimals) || 0;
                const prefix = el.dataset.prefix || '';
                const t0 = performance.now(), dur = 1400;
                (function tick(now) {
                    const p = Math.min((now - t0) / dur, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = prefix + (target * eased).toLocaleString('en-US', { minimumFractionDigits: dec, maximumFractionDigits: dec });
                    if (p < 1) requestAnimationFrame(tick);
                })(t0);
            });

            // --- Gráficas (carga Chart.js si no está disponible) ---
            function initCharts() {
                Chart.defaults.font.family = "Inter, sans-serif";
                Chart.defaults.color = '#78716c';

                const tooltip = {
                    backgroundColor: '#05201a', titleColor: '#fcd34d', bodyColor: '#ecfdf5',
                    padding: 12, cornerRadius: 10, displayColors: false, borderColor: 'rgba(252,211,77,.3)', borderWidth: 1
                };

                // 1. Línea: tendencia de peso
                const tendenciaLabels = @json($labelsTendencia ?? []);
                const tendenciaData = @json($dataTendencia ?? []);
                const lineCtx = document.getElementById('lineChart').getContext('2d');
                const grad = lineCtx.createLinearGradient(0, 0, 0, 300);
                grad.addColorStop(0, 'rgba(5, 150, 105, 0.35)');
                grad.addColorStop(1, 'rgba(5, 150, 105, 0)');

                new Chart(lineCtx, {
                    type: 'line',
                    data: {
                        labels: tendenciaLabels.length > 0 ? tendenciaLabels : ['Sin datos'],
                        datasets: [{
                            label: 'Peso promedio (kg)',
                            data: tendenciaData,
                            borderColor: '#047857',
                            backgroundColor: grad,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fbbf24',
                            pointBorderColor: '#05201a',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip },
                        scales: {
                            x: { grid: { display: false }, border: { display: false } },
                            y: { grid: { color: '#f5f5f4' }, border: { display: false }, beginAtZero: true }
                        }
                    }
                });

                // 2. Barras: inventario por categoría
                const distribucionData = @json($distribucionSexo ?? []);
                const labelsBar = distribucionData.map(i => i.categoria ? (i.categoria.charAt(0).toUpperCase() + i.categoria.slice(1)) : 'No especificado');
                const dataBar = distribucionData.map(i => i.total);

                new Chart(document.getElementById('doughnutChart'), {
                    type: 'bar',
                    data: {
                        labels: labelsBar,
                        datasets: [{
                            label: 'Cantidad de animales',
                            data: dataBar,
                            backgroundColor: ['#064e3b', '#047857', '#10b981', '#b45309', '#f59e0b', '#a8a29e'],
                            borderRadius: 10,
                            borderSkipped: false,
                            maxBarThickness: 64
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip },
                        scales: {
                            x: { grid: { display: false }, border: { display: false } },
                            y: { grid: { color: '#f5f5f4' }, border: { display: false }, beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });

                // 3. Dona: distribución por sexo
                const distribucionCategoriaData = @json($distribucionCategoria ?? []);
                const categoriaLabels = distribucionCategoriaData.map(i => i.sexo ? (i.sexo.charAt(0).toUpperCase() + i.sexo.slice(1)) : 'No especificado');
                const categoriaData = distribucionCategoriaData.map(i => i.total);

                new Chart(document.getElementById('doughnutChartCategoria'), {
                    type: 'doughnut',
                    data: {
                        labels: categoriaLabels,
                        datasets: [{
                            data: categoriaData,
                            backgroundColor: ['#064e3b', '#f59e0b', '#10b981', '#a8a29e'],
                            borderWidth: 4,
                            borderColor: '#ffffff',
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            tooltip: { ...tooltip, displayColors: true },
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, padding: 18, font: { size: 12 } }
                            }
                        }
                    }
                });
            }

            if (window.Chart) {
                initCharts();
            } else {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4';
                s.onload = initCharts;
                document.head.appendChild(s);
            }
        });
    </script>
</x-app-layout>
