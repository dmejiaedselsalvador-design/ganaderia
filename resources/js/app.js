import './bootstrap';

import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

window.Alpine = Alpine;
window.Swal = Swal;

/* ───────────── Alertas y confirmaciones con SweetAlert2 (adaptadas al tema) ─────────────
 * - window.alert(msg) ya no muestra la ventana del navegador: se ve como aviso del sistema.
 * - window.confirmar(msg, { peligro, titulo, boton }) → Promise<boolean>
 * - window.pedirTexto(titulo, { placeholder, minimo }) → Promise<string|null>
 * - En formularios o botones: data-confirm="¿Mensaje?"  (+ data-peligro, data-titulo, data-boton)
 *                              data-prompt="Motivo…" data-prompt-campo="motivo" (pide un texto y lo pone en ese campo)
 */
const varTema = (v, def) => getComputedStyle(document.body).getPropertyValue(v).trim() || def;
const Alerta = () => Swal.mixin({
    background: varTema('--t-surface', '#ffffff'),
    color: varTema('--t-text', '#1e293b'),
    confirmButtonColor: '#059669',
    cancelButtonColor: '#64748b',
    buttonsStyling: true,
    reverseButtons: true,
    focusCancel: false,
    customClass: { popup: 'rounded-2xl', input: 'swal-input-tema' },
});

window.alert = (mensaje) => {
    const texto = String(mensaje ?? '');
    const esError = /no |error|inválid|selecciona|ingresa|ya se encuentra|por favor/i.test(texto);
    Alerta().fire({ icon: esError ? 'warning' : 'info', text: texto, confirmButtonText: 'Entendido' });
};

window.confirmar = (mensaje, opciones = {}) => Alerta().fire({
    title: opciones.titulo ?? (opciones.peligro ? '¿Estás seguro?' : 'Confirmar'),
    text: mensaje,
    icon: opciones.peligro ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: opciones.boton ?? 'Sí, continuar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: opciones.peligro ? '#e11d48' : '#059669',
}).then((r) => r.isConfirmed);

window.pedirTexto = (titulo, opciones = {}) => Alerta().fire({
    title: titulo,
    input: 'text',
    inputPlaceholder: opciones.placeholder ?? '',
    showCancelButton: true,
    confirmButtonText: opciones.boton ?? 'Aceptar',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: opciones.peligro ? '#e11d48' : '#059669',
    inputValidator: (v) => (!v || v.trim().length < (opciones.minimo ?? 1)) ? (opciones.mensaje ?? 'Escribe el motivo.') : undefined,
}).then((r) => (r.isConfirmed ? r.value.trim() : null));

// Confirmación declarativa en formularios: <form data-confirm="..."> o <button data-confirm="...">
document.addEventListener('submit', async (e) => {
    const form = e.target;
    const boton = e.submitter;
    const origen = boton?.dataset.confirm || boton?.dataset.prompt ? boton : form;
    const mensaje = origen?.dataset?.confirm;
    const prompt = origen?.dataset?.prompt;
    if ((!mensaje && !prompt) || form.dataset.confirmado === '1') {
        delete form.dataset.confirmado;
        return;
    }
    e.preventDefault();
    e.stopImmediatePropagation();

    let ok = true;
    if (prompt) {
        const valor = await window.pedirTexto(prompt, { minimo: Number(origen.dataset.promptMinimo || 1), peligro: 'peligro' in origen.dataset });
        ok = valor !== null;
        if (ok) {
            const campo = form.querySelector(`[name="${origen.dataset.promptCampo || 'motivo'}"]`);
            if (campo) campo.value = valor;
        }
    } else {
        ok = await window.confirmar(mensaje, { peligro: 'peligro' in origen.dataset, titulo: origen.dataset.titulo, boton: origen.dataset.boton });
    }
    if (!ok) return;

    form.dataset.confirmado = '1';
    // Conserva el botón presionado (name/value, p. ej. anular=1) al reenviar
    if (boton && boton.name) {
        const oculto = document.createElement('input');
        oculto.type = 'hidden'; oculto.name = boton.name; oculto.value = boton.value;
        form.appendChild(oculto);
    }
    HTMLFormElement.prototype.submit.call(form);
}, true);

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

/**
 * Captura de la pantalla visible para el botón de Ayuda.
 * html2canvas se carga solo cuando se necesita (archivo aparte) para no hacer más pesada cada página.
 * Los elementos con [data-ayuda-ignorar] (el propio panel de ayuda) no salen en la imagen.
 */
window.capturarPantalla = async () => {
    const { default: html2canvas } = await import('html2canvas');
    const escala = Math.min(1, 1440 / window.innerWidth);
    const fondo = getComputedStyle(document.body).backgroundColor || '#ffffff';
    const canvas = await html2canvas(document.body, {
        x: window.scrollX, y: window.scrollY,
        width: window.innerWidth, height: window.innerHeight,
        windowWidth: window.innerWidth, windowHeight: window.innerHeight,
        scale: escala, backgroundColor: fondo, logging: false, useCORS: true,
        ignoreElements: (el) => el.hasAttribute && el.hasAttribute('data-ayuda-ignorar'),
    });
    return canvas.toDataURL('image/jpeg', 0.72);
};

Alpine.start();
