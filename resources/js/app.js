import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


import Swal from 'sweetalert2';
window.Swal = Swal;

import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

// Registrar el plugin globalmente (opcional pero recomendado)
Chart.register(ChartDataLabels);

// Hacer que Chart esté disponible de forma global en la ventana (por si inicializas las gráficas dentro de las vistas Blade)
window.Chart = Chart;
