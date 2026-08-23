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

        <!-- 1. FILA DE TARJETAS (KPIs) CON ICONOS -->
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

            <!-- Peso Promedio -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Peso Promedio</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($pesoGanados, 2) }} <span class="text-sm font-semibold text-slate-500">kg</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </div>
            </div>

            <!-- Listos Exportación -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Listos Exportación</p>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ $ganadoExportar }} <span class="text-sm font-semibold text-slate-500">Cabezas</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Costo Alimento -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-slate-300 transition-all flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Costo Alimento / Día</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">$0.00</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
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
