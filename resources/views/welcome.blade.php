<x-app-layout>
    <div id="vista-dashboard" class="space-y-6">

        <!-- 1. FILA DE TARJETAS (KPIs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total de Ganados</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $ganados }} Cabezas</h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Peso Promedio</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($pesoGanados,2) }} kg</h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Listos Exportación</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1 text-emerald-600">{{ $ganadoExportar }}</h3>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Costo Alimento / Día</p>
                <h3 class="text-2xl font-bold text-slate-800 mt-1">$0</h3>
            </div>
        </div>

        <!-- 2. FILA DE ANÁLISIS VISUAL -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Gráfica de Líneas: Tendencia de Peso -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Tendencia de Ganancia de Peso (kg)</h3>
                <canvas id="lineChart" class="h-64"></canvas>
            </div>
            <!-- Gráfica de Dona: Inventario por Lote -->
            <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Distribución por Genero</h3>
                <canvas id="doughnutChart" class="h-64"></canvas>
            </div>
        </div>
    </div>



  <script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- GRÁFICA DE LÍNEAS ---
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
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            }
        });

        // --- GRÁFICA DE DONA ---
        const distribucionData = @json($distribucionSexo ?? []);
        const labels = distribucionData.map(item => item.genero ? (item.genero.charAt(0).toUpperCase() + item.genero.slice(1)) : 'No especificado');
        const data = distribucionData.map(item => item.total);

        new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#3b82f6', '#ec4899', '#94a3b8']
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 14 },
                        formatter: (value) => value
                    }
                }
            }
        });
    });
</script>
</x-app-layout>
