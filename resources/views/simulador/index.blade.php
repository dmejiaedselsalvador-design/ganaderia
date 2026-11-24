<x-app-layout>
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{
            cabezas: {{ $numCabezas }},
            pesoIni: {{ $pesoInicialKg }},
            precioCompraKg: {{ $precioCompraKg }},
            pesoFin: {{ $pesoObjetivoKg }},
            gdp: {{ $gdpEsperadaKg }},
            costoAlimentoDia: {{ $costoAlimentoDia }},
            costoSanitario: {{ $costoSanitario }},
            flete: {{ $fleteYOtros }},
            precioVentaKg: {{ $precioVentaKg }},

            // Cálculos dinámicos
            get kgGanar() {
                return Math.max(0, this.pesoFin - this.pesoIni);
            },
            get diasEngorda() {
                return this.gdp > 0 ? Math.ceil(this.kgGanar / this.gdp) : 0;
            },
            get costoCompraAnimal() {
                return this.pesoIni * this.precioCompraKg;
            },
            get costoTotalAlimento() {
                return this.diasEngorda * this.costoAlimentoDia;
            },
            get costoTotalPorCabeza() {
                return this.costoCompraAnimal + this.costoTotalAlimento + Number(this.costoSanitario) + Number(this.flete);
            },
            get costoPorKgGanado() {
                return this.kgGanar > 0 ? ((this.costoTotalAlimento + Number(this.costoSanitario)) / this.kgGanar).toFixed(2) : 0;
            },
            get breakEvenKg() {
                return this.pesoFin > 0 ? (this.costoTotalPorCabeza / this.pesoFin).toFixed(2) : 0;
            },
            get ingresoPorCabeza() {
                return this.pesoFin * this.precioVentaKg;
            },
            get utilidadPorCabeza() {
                return this.ingresoPorCabeza - this.costoTotalPorCabeza;
            },
            get roi() {
                return this.costoTotalPorCabeza > 0 ? ((this.utilidadPorCabeza / this.costoTotalPorCabeza) * 100).toFixed(1) : 0;
            },
            // Totales de Lote
            get inversionLote() {
                return this.costoTotalPorCabeza * this.cabezas;
            },
            get ingresoLote() {
                return this.ingresoPorCabeza * this.cabezas;
            },
            get utilidadLote() {
                return this.utilidadPorCabeza * this.cabezas;
            },

            // Escenarios rápidos predefinidos
            cargarEscenario(tipo) {
                if (tipo === 'novillo_europeo') {
                    this.pesoIni = 240; this.precioCompraKg = 62; this.pesoFin = 500;
                    this.gdp = 1.65; this.costoAlimentoDia = 52; this.precioVentaKg = 66;
                } else if (tipo === 'cebu_repasto') {
                    this.pesoIni = 200; this.precioCompraKg = 55; this.pesoFin = 460;
                    this.gdp = 1.35; this.costoAlimentoDia = 44; this.precioVentaKg = 62;
                } else if (tipo === 'finalizacion_rapida') {
                    this.pesoIni = 340; this.precioCompraKg = 58; this.pesoFin = 490;
                    this.gdp = 1.75; this.costoAlimentoDia = 56; this.precioVentaKg = 65;
                }
            }
         }">

        <!-- ENCABEZADO -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-coins"></i>
                    </span>
                    Simulador Financiero & Calculadora de Engorda (MXN)
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Análisis de rentabilidad proyectada en Pesos Mexicanos: punto de equilibrio, costo por kilo ganado y margen neto.
                </p>
            </div>

            <!-- Botones de escenarios rápidos -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">Escenarios:</span>
                <button type="button" @click="cargarEscenario('novillo_europeo')"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Cruza Cárnica
                </button>
                <button type="button" @click="cargarEscenario('cebu_repasto')"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Repasto Cebú
                </button>
                <button type="button" @click="cargarEscenario('finalizacion_rapida')"
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                    Finalización 90d
                </button>
            </div>
        </div>

        <!-- 1. RESUMEN EJECUTIVO DEL LOTE EN PESOS MEXICANOS (MXN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Utilidad Neta Proyectada Lote -->
            <div class="p-5 rounded-2xl shadow-sm border transition-all"
                 :class="utilidadLote >= 0 ? 'bg-gradient-to-br from-emerald-600 to-emerald-800 text-white border-emerald-500' : 'bg-gradient-to-br from-rose-600 to-rose-800 text-white border-rose-500'">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-100">Utilidad Neta del Lote</p>
                <h3 class="text-2xl sm:text-3xl font-black font-mono mt-1"
                    x-text="'$ ' + Number(utilidadLote).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' MXN'">
                </h3>
                <p class="text-xs text-emerald-100/80 mt-1" x-text="'ROI: ' + roi + '% sobre la inversión'"></p>
            </div>

            <!-- Utilidad Neta por Cabeza -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ganancia / Cabeza</p>
                    <h3 class="text-2xl font-black font-mono mt-1"
                        :class="utilidadPorCabeza >= 0 ? 'text-emerald-700' : 'text-rose-600'"
                        x-text="'$ ' + Number(utilidadPorCabeza).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' MXN'">
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Margen libre por animal</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>

            <!-- Punto de Equilibrio ($/kg Venta) -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Punto de Equilibrio</p>
                    <h3 class="text-2xl font-black text-slate-800 font-mono mt-1"
                        x-text="'$ ' + breakEvenKg + ' MXN'">
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Precio mínimo de venta / kg</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>
            </div>

            <!-- Costo por Kilo Ganado -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Costo / Kg Producido</p>
                    <h3 class="text-2xl font-black text-amber-700 font-mono mt-1"
                        x-text="'$ ' + costoPorKgGanado + ' MXN'">
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Alimento + sanidad por kg</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- 2. PANEL INTERACTIVO DE VARIABLES Y DESGLOSE -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- FORMULARIO DE VARIABLES DE ENTRADA -->
            <div class="lg:col-span-7 bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-emerald-600"></i> Parámetros de Compra y Manejo
                    </h3>
                    <span class="text-xs text-slate-400">Moneda: Pesos Mexicanos (MXN)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <!-- Número de cabezas -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tamaño del Lote (Cabezas)</label>
                        <input type="number" x-model.number="cabezas" min="1" max="1000"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- GDP Esperada -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">GDP Esperada (kg/día)</label>
                        <input type="number" step="0.05" x-model.number="gdp" min="0.2" max="3"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Peso Compra y Precio Compra -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peso de Compra Inicial (kg)</label>
                        <input type="number" step="5" x-model.number="pesoIni" min="100" max="600"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Precio Compra ($ MXN/kg en pie)</label>
                        <input type="number" step="0.5" x-model.number="precioCompraKg" min="10" max="150"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Peso Objetivo Venta y Precio Venta -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peso Objetivo Venta / Faena (kg)</label>
                        <input type="number" step="5" x-model.number="pesoFin" min="200" max="900"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Precio Venta ($ MXN/kg en pie)</label>
                        <input type="number" step="0.5" x-model.number="precioVentaKg" min="10" max="150"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Costos Diarios y Fijos -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Costo Alimento ($ MXN/cabeza/día)</label>
                        <input type="number" step="1" x-model.number="costoAlimentoDia" min="5" max="200"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Sanidad + Implantes ($ MXN/cab)</label>
                        <input type="number" step="10" x-model.number="costoSanitario" min="0" max="2000"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Flete y Manejo de Recepción ($ MXN/cabeza)</label>
                        <input type="number" step="10" x-model.number="flete" min="0" max="2000"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- DESGLOSE DETALLADO FINANCIERO -->
            <div class="lg:col-span-5 bg-white rounded-2xl shadow-xs border border-slate-200 p-6 flex flex-col justify-between space-y-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-emerald-600"></i> Desglose Económico por Cabeza
                    </h3>

                    <div class="space-y-2.5 text-xs sm:text-sm pt-2">
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Días en engorda estimados:</span>
                            <span class="font-bold text-slate-800 font-mono" x-text="diasEngorda + ' días'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Kilos ganados en corral:</span>
                            <span class="font-bold text-emerald-700 font-mono" x-text="kgGanar + ' kg'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Costo de compra del becerro:</span>
                            <span class="font-bold text-slate-800 font-mono"
                                  x-text="'$ ' + Number(costoCompraAnimal).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Costo total de alimentación:</span>
                            <span class="font-bold text-amber-700 font-mono"
                                  x-text="'$ ' + Number(costoTotalAlimento).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Gastos sanitarios y flete:</span>
                            <span class="font-bold text-slate-700 font-mono"
                                  x-text="'$ ' + Number(Number(costoSanitario) + Number(flete)).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                        </div>
                        <div class="flex justify-between py-2 border-t-2 border-slate-200 text-slate-900 font-bold">
                            <span>Inversión Total por Cabeza:</span>
                            <span class="font-mono text-emerald-900"
                                  x-text="'$ ' + Number(costoTotalPorCabeza).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                        </div>
                        <div class="flex justify-between py-2 bg-emerald-50 px-3 rounded-xl border border-emerald-100">
                            <span class="font-semibold text-emerald-900">Ingreso por Venta ({{ number_format(490, 0) }} kg):</span>
                            <span class="font-black text-emerald-900 font-mono"
                                  x-text="'$ ' + Number(ingresoPorCabeza).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                        </div>
                    </div>
                </div>

                <!-- Resumen Lote Completo -->
                <div class="bg-slate-900 text-white p-4 rounded-xl space-y-1.5 text-xs">
                    <span class="text-[10px] uppercase font-bold text-emerald-400 block tracking-wider">Totales para el lote (x<span x-text="cabezas"></span> cabezas)</span>
                    <div class="flex justify-between text-slate-300">
                        <span>Capital Requerido:</span>
                        <span class="font-mono font-bold" x-text="'$ ' + Number(inversionLote).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Ingreso Bruto de Venta:</span>
                        <span class="font-mono font-bold" x-text="'$ ' + Number(ingresoLote).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                    </div>
                    <div class="flex justify-between text-base font-extrabold text-emerald-300 pt-1 border-t border-slate-800">
                        <span>Margen Neto Total:</span>
                        <span class="font-mono" x-text="'$ ' + Number(utilidadLote).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
