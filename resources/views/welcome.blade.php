<x-app-layout>
    <div id="vista-dashboard" class="py-6 space-y-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- ENCABEZADO DEL DASHBOARD -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Panel de Control Ganadero</h1>
                <p class="text-sm text-slate-500 mt-1">Monitoreo en tiempo real de inventario, peso y métricas del hato.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Sistema Activo
                </span>
            </div>
        </div>

        @if(($listosVenta ?? 0) > 0)
            <!-- ALERTA DE GANADO LISTO PARA VENDER -->
            <div class="p-4 bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl text-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg shrink-0">
                        🎯
                    </div>
                    <div>
                        <strong class="font-bold text-sm block">¡Hay {{ $listosVenta }} cabezas de ganado que alcanzaron su peso objetivo!</strong>
                        <span class="text-xs text-amber-100">Cumplen con el peso meta de venta para su categoría y están listos para comercialización.</span>
                    </div>
                </div>
                <a href="{{ route('compras.ganado.index', ['filtro' => 'listos_venta']) }}"
                   class="bg-white hover:bg-slate-50 text-amber-900 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm shrink-0 flex items-center gap-1.5">
                    Ver Animales Listos <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        @endif

        @if(($animalesEnRetiroCount ?? 0) > 0)
            <!-- ALERTA DE PERIODO DE RETIRO SANITARIO -->
            <div class="p-4 bg-gradient-to-r from-rose-600 to-rose-700 rounded-2xl text-white shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <div>
                        <strong class="font-bold text-sm block">¡Alerta Sanitaria: {{ $animalesEnRetiroCount }} cabezas bajo periodo de retiro de fármacos!</strong>
                        <span class="text-xs text-rose-100">Tienen tratamientos médicos activos y NO deben enviarse a rastro hasta cumplir el tiempo reglamentario.</span>
                    </div>
                </div>
                <a href="{{ route('sanidad.index', ['filtro' => 'en_retiro']) }}"
                   class="bg-white hover:bg-slate-50 text-rose-900 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm shrink-0 flex items-center gap-1.5">
                    Consultar Retiros <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        @endif

        <!-- 1. FILA DE TARJETAS (KPIs) PRINCIPALES -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Ganado -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total de Ganado</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $ganados }} <span class="text-sm font-semibold text-slate-500">Cabezas</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>

            <!-- Inversión en Ganado en Pie (MXN) -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Inversión Ganado en Pie</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-700 font-mono mt-1">{{ formatoPesos($inversionGanadoPieMXN ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-cow text-xl"></i>
                </div>
            </div>

            <!-- Inversión en Alimento Servido (MXN) -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Alimento Servido</p>
                    <h3 class="text-xl sm:text-2xl font-black text-amber-700 font-mono mt-1">{{ formatoPesos($inversionAlimentoMXN ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-wheat-awn text-xl"></i>
                </div>
            </div>

            <!-- Ingresos Ventas MXN -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ingresos por Ventas</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-800 font-mono mt-1">{{ formatoPesos($ingresosVentasMXN ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                </div>
            </div>
        </div>

        <!-- 2. KPIs ZOOTÉCNICOS & ENGORDA -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-100">
                <span class="text-[11px] font-semibold uppercase text-slate-400 block">Peso Promedio Actual</span>
                <div class="text-xl font-black text-slate-800 font-mono mt-0.5">
                    {{ number_format($pesoGanados, 1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-100">
                <span class="text-[11px] font-semibold uppercase text-slate-400 block">Corrales Activos</span>
                <div class="text-xl font-black text-indigo-700 mt-0.5">
                    {{ $totalCorralesActivos ?? 0 }} <span class="text-xs font-normal text-slate-400">lotes</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-100">
                <span class="text-[11px] font-semibold uppercase text-slate-400 block">Rend. Canal Promedio</span>
                <div class="text-xl font-black text-rose-700 font-mono mt-0.5">
                    {{ $rendimientoPromedioCanal ?? 60.0 }}%
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-100">
                <span class="text-[11px] font-semibold uppercase text-slate-400 block">Cabezas Exportación</span>
                <div class="text-xl font-black text-emerald-700 mt-0.5">
                    {{ $ganadoExportar }} <span class="text-xs font-normal text-slate-400">(>200 kg)</span>
                </div>
            </div>
        </div>

        <!-- ACCESOS RÁPIDOS A MÓDULOS ZOOTÉCNICOS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('corrales.index') }}"
               class="p-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition flex items-center gap-2.5 text-xs font-semibold text-slate-700 shadow-2xs">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-warehouse"></i>
                </span>
                <span>Feedlot / Corrales</span>
            </a>

            <a href="{{ route('nutricion.index') }}"
               class="p-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition flex items-center gap-2.5 text-xs font-semibold text-slate-700 shadow-2xs">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-wheat-awn"></i>
                </span>
                <span>Nutrición & Dietas</span>
            </a>

            <a href="{{ route('calidad.carne.index') }}"
               class="p-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition flex items-center gap-2.5 text-xs font-semibold text-slate-700 shadow-2xs">
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-drumstick-bite"></i>
                </span>
                <span>Calidad Carne & Canal</span>
            </a>

            <a href="{{ route('simulador.index') }}"
               class="p-3 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition flex items-center gap-2.5 text-xs font-semibold text-slate-700 shadow-2xs">
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calculator"></i>
                </span>
                <span>Simulador Engorda MXN</span>
            </a>
        </div>

        <!-- 2. FILA DE ANÁLISIS VISUAL (GRÁFICAS) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Gráfica de Líneas: Tendencia de Peso (Ocupa 2 columnas) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800">Tendencia de Ganancia de Peso</h3>
                    <span class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">Últimos meses</span>
                </div>
                <div class="relative w-full h-72">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Gráfica de Dona: Distribución por Sexo -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800">Distribución por Sexo</h3>
                </div>
                <div class="relative w-full h-72 flex items-center justify-center">
                    <canvas id="doughnutChartCategoria"></canvas>
                </div>
            </div>

            <!-- Gráfica de Barras: Distribución por Categoría (Ocupa las 3 columnas o se ajusta) -->
            <div class="lg:col-span-3 bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800">Inventario Detallado por Categoría</h3>
                    <span class="text-xs text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">Activos en el hato</span>
                </div>
                <div class="relative w-full h-72">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPTS DE LAS GRÁFICAS -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- 1. GRÁFICA DE LÍNEAS: Tendencia de Peso ---
            const tendenciaLabels = @json($labelsTendencia ?? []);
            const tendenciaData = @json($dataTendencia ?? []);

            new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: {
                    labels: tendenciaLabels.length > 0 ? tendenciaLabels : ['Sin datos'],
                    datasets: [{
                        label: 'Peso Promedio (kg)',
                        data: tendenciaData,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.05)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { grid: { color: '#f1f5f9' }, beginAtZero: true }
                    }
                }
            });

            // --- 2. GRÁFICA DE BARRAS: Inventario por Categoría ---
            const distribucionData = @json($distribucionSexo ?? []);
            const labelsBar = distribucionData.map(item => item.categoria ? (item.categoria.charAt(0).toUpperCase() + item.categoria.slice(1)) : 'No especificado');
            const dataBar = distribucionData.map(item => item.total);

            new Chart(document.getElementById('doughnutChart'), {
                type: 'bar',
                data: {
                    labels: labelsBar,
                    datasets: [{
                        label: 'Cantidad de Animales',
                        data: dataBar,
                        backgroundColor: ['#3b82f6', '#ec4899', '#10b981', '#f43f5e', '#8b5cf6', '#f59e0b'],
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });

            // --- 3. GRÁFICA DE DONA: Distribución por Sexo ---
            const distribucionCategoriaData = @json($distribucionCategoria ?? []);
            const categoriaLabels = distribucionCategoriaData.map(item => item.sexo ? (item.sexo.charAt(0).toUpperCase() + item.sexo.slice(1)) : 'No especificado');
            const categoriaData = distribucionCategoriaData.map(item => item.total);

            new Chart(document.getElementById('doughnutChartCategoria'), {
                type: 'doughnut',
                data: {
                    labels: categoriaLabels,
                    datasets: [{
                        data: categoriaData,
                        backgroundColor: ['#3b82f6', '#ec4899', '#10b981', '#f43f5e'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: { size: 11, family: 'sans-serif' }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
