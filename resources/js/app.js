import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

window.Alpine = Alpine;
window.Swal = Swal;

// Chart.js global (las vistas Blade crean sus gráficas con window.Chart)
Chart.register(ChartDataLabels);
// Las etiquetas de datos solo se muestran donde cada gráfica las active
Chart.defaults.plugins.datalabels = Object.assign(Chart.defaults.plugins.datalabels || {}, { display: false });
window.Chart = Chart;

/* =====================================================================
   TEMAS: claro | oscuro | metal
   ===================================================================== */
const TEMAS = ['claro', 'oscuro', 'metal'];

function leerVar(nombre, defecto) {
    const v = getComputedStyle(document.body).getPropertyValue(nombre).trim();
    return v || defecto;
}

/** Colores de gráficas según el tema activo. */
window.temaChart = function () {
    return {
        texto: leerVar('--t-chart-text', '#64748b'),
        grid: leerVar('--t-chart-grid', '#f1f5f9'),
        superficie: leerVar('--t-surface', '#ffffff'),
        oscuro: document.body.classList.contains('tema-oscuro'),
    };
};

function aplicarTemaGraficas() {
    const t = window.temaChart();
    Chart.defaults.color = t.texto;
    Chart.defaults.borderColor = t.grid;
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";

    Object.values(Chart.instances || {}).forEach((chart) => {
        Object.values(chart.options.scales || {}).forEach((escala) => {
            if (escala.grid) escala.grid.color = t.grid;
            if (escala.ticks) escala.ticks.color = t.texto;
        });
        if (chart.options.plugins?.legend?.labels) chart.options.plugins.legend.labels.color = t.texto;
        chart.data.datasets.forEach((ds) => {
            if (chart.config.type === 'doughnut' || chart.config.type === 'pie') ds.borderColor = t.superficie;
        });
        chart.update('none');
    });
}

window.cambiarTema = function (tema) {
    if (!TEMAS.includes(tema)) return;

    const body = document.body;
    body.classList.remove('tema-claro', 'tema-oscuro', 'tema-dark', 'tema-metal');
    body.classList.add('tema-' + tema);
    if (tema === 'oscuro') body.classList.add('tema-dark'); // alias para vistas antiguas
    body.dataset.tema = tema;

    document.querySelectorAll('[data-tema-btn]').forEach((btn) => {
        const activo = btn.dataset.temaBtn === tema;
        btn.classList.toggle('bg-white', activo);
        btn.classList.toggle('shadow-sm', activo);
        btn.classList.toggle('text-slate-900', activo);
        btn.classList.toggle('text-slate-500', !activo);
    });

    aplicarTemaGraficas();
    document.dispatchEvent(new CustomEvent('tema-cambiado', { detail: { tema } }));

    // Persistir (usuario + cookie para la pantalla de login)
    window.axios.post('/preferencias/tema', { tema }).catch(() => {
        document.cookie = 'tema=' + tema + ';path=/;max-age=31536000;SameSite=Lax';
    });
};

document.addEventListener('DOMContentLoaded', aplicarTemaGraficas);
// Re-aplicar cuando las vistas terminen de crear sus gráficas
window.addEventListener('load', aplicarTemaGraficas);

Alpine.start();
