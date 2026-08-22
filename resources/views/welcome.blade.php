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
        const labels = distribucionData.map(item => item.categoria ? (item.categoria.charAt(0).toUpperCase() + item.categoria.slice(1)) : 'No especificado');
        const data = distribucionData.map(item => item.total);

        new Chart(document.getElementById('doughnutChart'), { // Puedes cambiar el id del canvas si gustas, ej. 'barChart'
        type: 'bar', // Cambiamos de 'doughnut' a 'bar'
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Animales',
                data: data,
                backgroundColor: [
                    '#3b82f6', // Becerro (Azul)
                    '#ec4899', // Becerra (Rosa)
                    '#10b981', // Torete (Esmeralda)
                    '#f43f5e', // Vaquilla (Rosa oscuro/Rojo)
                    '#8b5cf6', // Toro (Morado)
                    '#f59e0b'  // Vaca (Ámbar/Naranja)
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false // En las barras por lo general se oculta la leyenda porque las etiquetas ya están abajo en el eje X
                },
                datalabels: {
                    color: '#4b5563', // Color de texto visible sobre las barras
                    anchor: 'end',
                    align: 'top',
                    font: { weight: 'bold', size: 12 },
                    formatter: (value) => value
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0 // Evita que salgan decimales en el conteo de animales (ej. 1.5 animales)
                    }
                }
            }
        }
    });
    });
</script>
</x-app-layout>
