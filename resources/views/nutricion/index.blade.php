<x-app-layout>
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{ modalRacionOpen: false, modalServidoOpen: false }">

        <!-- Mensajes Flash -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-center gap-3 text-emerald-800 text-sm shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-sm shadow-xs">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Por favor verifique los siguientes campos:
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ENCABEZADO -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-wheat-awn"></i>
                    </span>
                    Nutrición, Raciones y Conversión Alimenticia
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Fases de engorda en corral, formulación de dietas, costo por kilogramo ganado y control de comederos.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <button @click="modalRacionOpen = true"
                        class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Nueva Dieta
                </button>
                <button @click="modalServidoOpen = true"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-utensils"></i> Servir Alimento
                </button>
            </div>
        </div>

        <!-- 1. KPIs NUTRICIONALES Y FINANCIEROS EN PESOS MEXICANOS (MXN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Inversión Total en Alimento -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Inversión en Alimento</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-700 font-mono mt-1">
                        {{ formatoPesos($inversionTotalAlimentoMXN) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Gasto acumulado en dietas</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>

            <!-- Kilos Consumidos -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Volumen Servido</p>
                    <h3 class="text-2xl font-black text-slate-800 font-mono mt-1">
                        {{ number_format($totalKilosConsumidos, 1) }} <span class="text-sm font-medium text-slate-500">kg</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Consumidos en comederos</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-weight-hanging"></i>
                </div>
            </div>

            <!-- Costo Promedio por Kg Alimento -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Costo Promedio / Kg</p>
                    <h3 class="text-2xl font-black text-slate-800 font-mono mt-1">
                        {{ formatoPesos($costoPromedioKgAlimento) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Por kg de ración base fresca</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-tag"></i>
                </div>
            </div>

            <!-- Conversión Alimenticia (FCR) -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Conversión (FCR)</p>
                    <h3 class="text-2xl font-black text-indigo-700 font-mono mt-1">
                        {{ $conversionAlimenticiaGlobal > 0 ? $conversionAlimenticiaGlobal . ' : 1' : 'En cálculo' }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">kg alimento por kg de carne</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-calculator"></i>
                </div>
            </div>
        </div>

        <!-- 2. CALCULADORA ZOOTÉCNICA EN VIVO & NOTA TÉCNICA ENGORMIX -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
             x-data="{
                pesoVivo: 350,
                pctCMS: 2.8,
                gdpEsperada: 1.5,
                costoKgDieta: 6.80,
                get consumoKgDia() {
                    return ((this.pesoVivo * (this.pctCMS / 100))).toFixed(2);
                },
                get costoDiarioMXN() {
                    return (this.consumoKgDia * this.costoKgDieta).toFixed(2);
                },
                get conversionEst() {
                    return (this.gdpEsperada > 0 ? (this.consumoKgDia / this.gdpEsperada).toFixed(2) : 0);
                },
                get costoKgGanadoMXN() {
                    return (this.gdpEsperada > 0 ? (this.costoDiarioMXN / this.gdpEsperada).toFixed(2) : 0);
                }
             }">

            <!-- Calculadora en Vivo -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-emerald-600"></i> Calculadora de Ración y Conversión en Tiempo Real
                    </h3>
                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg font-bold">Zootecnia Interactiva</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peso Vivo Animal (kg)</label>
                        <input type="number" step="10" x-model.number="pesoVivo"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Consumo MS (% PV)</label>
                        <input type="number" step="0.1" x-model.number="pctCMS"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">GDP Esperada (kg/día)</label>
                        <input type="number" step="0.1" x-model.number="gdpEsperada"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Costo Dieta ($ MXN/kg)</label>
                        <input type="number" step="0.1" x-model.number="costoKgDieta"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Resultados instantáneos -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Consumo Diario</span>
                        <span class="text-lg font-black text-slate-800 font-mono" x-text="consumoKgDia + ' kg'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Costo Diario / Cabeza</span>
                        <span class="text-lg font-black text-amber-700 font-mono" x-text="'$ ' + costoDiarioMXN + ' MXN'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Conversión (CA)</span>
                        <span class="text-lg font-black text-indigo-700 font-mono" x-text="conversionEst + ' : 1'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Costo / Kg Ganado</span>
                        <span class="text-lg font-black text-emerald-700 font-mono" x-text="'$ ' + costoKgGanadoMXN + ' MXN'"></span>
                    </div>
                </div>
            </div>

            <!-- Cuadro Informativo de Engormix (Dra. Salud Rubio) -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 flex flex-col justify-between shadow-xs">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-400 block">Fundamento Técnico · Engormix</span>
                    <h4 class="font-extrabold text-white text-base">Impacto de la Dieta en la Calidad de la Carne</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        De acuerdo con la Dra. María Salud Rubio (UNAM), una dieta alta en energía durante la finalización (>75% grano) incrementa la reserva de <strong>glucógeno muscular</strong>, asegurando el descenso ideal del <strong>pH a 5.4 - 5.7</strong> post-sacrificio.
                    </p>
                    <ul class="text-[11px] text-slate-300 space-y-1 list-disc list-inside pt-1">
                        <li>Mejora el marmoleo (grasa intramuscular).</li>
                        <li>Genera cobertura de grasa dorsal protectora.</li>
                        <li>Evita el corte oscuro DFD por agotamiento.</li>
                    </ul>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-700 text-[10px] text-slate-400 flex items-center justify-between">
                    <span>Fases recomendadas: 90 - 120 días</span>
                    <i class="fa-solid fa-certificate text-emerald-400"></i>
                </div>
            </div>
        </div>

        <!-- 3. CATÁLOGO DE DIETAS Y RACIONES TÉCNICAS -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-600"></i> Dietas y Fórmulas Registradas
                    </h3>
                    <p class="text-xs text-slate-500">Parámetros bromatológicos y costos unitarios en Pesos Mexicanos (MXN).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @forelse($raciones as $racion)
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/50 hover:bg-white hover:border-emerald-300 transition-all">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="font-mono text-xs font-bold text-slate-500 bg-slate-200 px-2 py-0.5 rounded">{{ $racion->codigo }}</span>
                                <h4 class="font-extrabold text-slate-800 text-sm mt-1.5">{{ $racion->nombre }}</h4>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $racion->fase === 'Finalizacion' ? 'bg-rose-100 text-rose-800' : ($racion->fase === 'Crecimiento' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $racion->fase }}
                            </span>
                        </div>

                        <div class="text-2xl font-black text-emerald-700 font-mono">
                            {{ formatoPesos($racion->costo_por_kg) }} <span class="text-xs text-slate-400 font-normal">/ kg</span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center text-xs pt-2 border-t border-slate-200">
                            <div>
                                <span class="text-[10px] text-slate-400 block">% MS</span>
                                <span class="font-bold text-slate-700 font-mono">{{ $racion->materia_seca_pct }}%</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">% Prot. C.</span>
                                <span class="font-bold text-slate-700 font-mono">{{ $racion->proteina_cruda_pct }}%</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Mcal/kg</span>
                                <span class="font-bold text-slate-700 font-mono">{{ $racion->energia_metabolizable_mcal }}</span>
                            </div>
                        </div>

                        @if($racion->descripcion_ingredientes)
                            <p class="text-[11px] text-slate-500 italic bg-white p-2.5 rounded-xl border border-slate-100">
                                {{ $racion->descripcion_ingredientes }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 italic">
                        No hay dietas registradas. Haga clic en "Nueva Dieta" para agregar la fórmula de Recepción o Finalización.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 4. HISTORIAL RECIENTE DE COMEDEROS / SERVIDOS -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> Últimos Servidos de Alimento Registrados
                </h3>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                            <th class="p-3">Fecha</th>
                            <th class="p-3">Corral / Lote</th>
                            <th class="p-3">Ración</th>
                            <th class="p-3">Kg Servidos</th>
                            <th class="p-3">Kg Rechazo</th>
                            <th class="p-3">Kg Consumidos</th>
                            <th class="p-3">Kg / Cabeza</th>
                            <th class="p-3">Costo Total (MXN)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($consumosRecientes as $consumo)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-semibold text-slate-800">{{ $consumo->fecha->format('d/m/Y') }}</td>
                                <td class="p-3 font-semibold text-slate-700">{{ $consumo->corralLote->nombre }}</td>
                                <td class="p-3 text-slate-600">{{ $consumo->racion->nombre }}</td>
                                <td class="p-3 font-mono text-slate-700">{{ number_format($consumo->kilos_servidos, 1) }} kg</td>
                                <td class="p-3 font-mono text-slate-400">{{ number_format($consumo->kilos_rechazo, 1) }} kg</td>
                                <td class="p-3 font-mono font-bold text-slate-900">{{ number_format($consumo->kilos_consumidos, 1) }} kg</td>
                                <td class="p-3 font-mono text-slate-700">{{ number_format($consumo->consumo_promedio_cabeza_kg, 2) }} kg</td>
                                <td class="p-3 font-mono font-bold text-emerald-700">{{ formatoPesos($consumo->costo_total_mxn) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 italic">
                                    No hay registros recientes de consumo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= MODAL: NUEVA DIETA / RACIÓN ================= -->
        <div x-show="modalRacionOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalRacionOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-wheat-awn text-emerald-600"></i> Registrar Nueva Dieta en Catálogo
                    </h3>
                    <button @click="modalRacionOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('nutricion.racion.store') }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Código Ración *</label>
                            <input type="text" name="codigo" required placeholder="Ej. RAC-REC-01"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono uppercase">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fase de Engorda *</label>
                            <select name="fase" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                <option value="Recepcion">Recepción / Adaptación</option>
                                <option value="Crecimiento">Crecimiento / Desarrollo</option>
                                <option value="Finalizacion" selected>Finalización Alta Energía</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nombre Comercial de la Dieta *</label>
                        <input type="text" name="nombre" required placeholder="Ej. Dieta Finalización 80% Grano con Melaza"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Costo por Kg ($ MXN) *</label>
                            <input type="number" step="0.05" name="costo_por_kg" value="6.80" min="0.1" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Materia Seca (% MS) *</label>
                            <input type="number" step="0.1" name="materia_seca_pct" value="88.0" min="10" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Proteína Cruda (% PC) *</label>
                            <input type="number" step="0.1" name="proteina_cruda_pct" value="13.5" min="5" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Energía Metab. (Mcal/kg) *</label>
                            <input type="number" step="0.05" name="energia_metabolizable_mcal" value="2.90" min="1" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Ingredientes Principales</label>
                        <textarea name="descripcion_ingredientes" rows="2" placeholder="Ej. Maíz quebrado (65%), grano destilería DDGS (15%), heno alfalfa (12%), melaza (5%), premezcla mineral y buffer (3%)..."
                                  class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalRacionOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Guardar Dieta
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL: SERVIR ALIMENTO A CORRAL ================= -->
        <div x-show="modalServidoOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalServidoOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-utensils text-emerald-600"></i> Servir Alimento a Corral
                    </h3>
                    <button @click="modalServidoOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('nutricion.consumo.store') }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Corral Destino *</label>
                        <select name="corral_lote_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            @foreach($corrales as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->codigo }} - {{ $c->nombre }} ({{ $c->total_cabezas }} cabezas)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Dieta a Servir *</label>
                        <select name="racion_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            @foreach($raciones as $racion)
                                <option value="{{ $racion->id }}">
                                    {{ $racion->nombre }} ({{ formatoPesos($racion->costo_por_kg) }}/kg)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha de Servido *</label>
                            <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kilos Servidos *</label>
                            <input type="number" step="0.1" name="kilos_servidos" placeholder="Ej. 400.0" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kilos Rechazo (Sobrante)</label>
                        <input type="number" step="0.1" name="kilos_rechazo" value="0.0"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Condiciones del comedero..."
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalServidoOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Registrar Consumo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
