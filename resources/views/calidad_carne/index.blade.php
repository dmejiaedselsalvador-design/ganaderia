<x-app-layout>
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{ modalCanalOpen: false }">

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
                    <span class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-drumstick-bite"></i>
                    </span>
                    Calidad de Carne Bovina y Rendimiento en Canal
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Metodología de tipificación cárnica (Dra. María Salud Rubio Lozano · UNAM / Engormix & NMX-FF-078-SCFI).
                </p>
            </div>
            <button @click="modalCanalOpen = true"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-plus"></i> Registrar Evaluación de Canal
            </button>
        </div>

        <!-- 1. KPIs DE RENDIMIENTO Y CALIDAD EN MONEDA NACIONAL (MXN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Rendimiento Promedio PCC -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rendimiento Promedio (PCC)</p>
                    <h3 class="text-2xl font-black text-slate-800 font-mono mt-1">
                        {{ $rendimientoPromedioPCC }}%
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Canal Caliente / Peso Vivo</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-percent"></i>
                </div>
            </div>

            <!-- pH Promedio 24h -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">pH Muscular a 24h</p>
                    <h3 class="text-2xl font-black font-mono mt-1 {{ $phPromedio <= 5.75 && $phPromedio >= 5.40 ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $phPromedio > 0 ? $phPromedio : '5.55 (Ref)' }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Rango óptimo: 5.40 - 5.75</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-vial"></i>
                </div>
            </div>

            <!-- Canales Óptimas vs DFD -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Estado pH Post-Faena</p>
                    <div class="text-base font-black text-slate-800 mt-1 flex items-center gap-2">
                        <span class="text-emerald-600 font-mono">{{ $canalesOptimasCount }} Óptimas</span>
                        <span class="text-slate-300">|</span>
                        <span class="text-rose-600 font-mono">{{ $canalesDfdCount }} DFD</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Corte Oscuro prevenido</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <!-- Valor Total Canales Evaluadas en MXN -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valor Canales (MXN)</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-700 font-mono mt-1">
                        {{ formatoPesos($valorTotalCanalesMXN) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $totalCanales }} canales liquidadas</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <!-- 2. SIMULADOR DE RENDIMIENTO EN CANAL & GUÍA TÉCNICA ENGORMIX -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
             x-data="{
                pesoPie: 490,
                pctRendimiento: 60.5,
                pctMerma: 2.0,
                precioKgCanal: 115.0,
                get pccKg() {
                    return ((this.pesoPie * (this.pctRendimiento / 100))).toFixed(1);
                },
                get pcfKg() {
                    return ((this.pccKg * (1 - (this.pctMerma / 100)))).toFixed(1);
                },
                get valorCanalMXN() {
                    return ((this.pccKg * this.precioKgCanal)).toFixed(2);
                }
             }">

            <!-- Simulador de Rendimiento -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-rose-600"></i> Simulador de Rendimiento en Canal y Merma Fría
                    </h3>
                    <span class="text-xs bg-rose-50 text-rose-700 px-2.5 py-1 rounded-lg font-bold">Herramienta Comercial</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Peso en Pie Báscula (kg)</label>
                        <input type="number" step="10" x-model.number="pesoPie"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Rend. Caliente (PCC %)</label>
                        <input type="number" step="0.5" x-model.number="pctRendimiento"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Merma Oreo Cámara (%)</label>
                        <input type="number" step="0.1" x-model.number="pctMerma"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Precio Canal ($ MXN/kg)</label>
                        <input type="number" step="1" x-model.number="precioKgCanal"
                               class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-rose-500">
                    </div>
                </div>

                <!-- Resultados instantáneos -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Canal Caliente (PCC)</span>
                        <span class="text-lg font-black text-slate-800 font-mono" x-text="pccKg + ' kg'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Canal Fría (PCF 24h)</span>
                        <span class="text-lg font-black text-indigo-700 font-mono" x-text="pcfKg + ' kg'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Kilos Merma Evaporación</span>
                        <span class="text-lg font-black text-slate-500 font-mono" x-text="(pccKg - pcfKg).toFixed(1) + ' kg'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Liquidación Estimada</span>
                        <span class="text-lg font-black text-emerald-700 font-mono" x-text="'$ ' + Number(valorCanalMXN).toLocaleString('es-MX', {minimumFractionDigits: 2}) + ' MXN'"></span>
                    </div>
                </div>
            </div>

            <!-- Panel Científico: Parámetros Dra. Salud Rubio (Engormix) -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 flex flex-col justify-between shadow-xs">
                <div class="space-y-2">
                    <span class="text-[10px] uppercase font-bold tracking-widest text-rose-400 block">Ciencia Cárnica · Engormix</span>
                    <h4 class="font-extrabold text-white text-base">Factores Críticos de Calidad y pH</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Según la Dra. María Salud Rubio Lozano (UNAM), los 3 factores que más impactan la terneza y el color comercial de la carne son:
                    </p>
                    <div class="space-y-1.5 text-[11px] text-slate-300 pt-1">
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">1. pH 24h:</span>
                            <span>Debe llegar a 5.4 - 5.7. Si el ganado se estresa en el transporte, el glucógeno se agota y el pH no baja (>5.8 DFD).</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-amber-400 font-bold">2. Grasa Dorsal:</span>
                            <span>Mínimo 5-8 mm para aislar la canal y evitar el acortamiento por frío (cold shortening) en cámara.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-rose-400 font-bold">3. Marmoleo:</span>
                            <span>Grasa intramuscular estimulada por dietas de engorda a base de granos en corral.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TABLA DE EVALUACIONES DE CANAL REGISTRADAS -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check text-rose-600"></i> Historial de Canales Tipificadas
                    </h3>
                    <p class="text-xs text-slate-500">Evaluaciones de rendimiento, pH a 24 horas y clasificación oficial en rastro.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                            <th class="p-3">Folio Canal</th>
                            <th class="p-3">Arete</th>
                            <th class="p-3">Fecha Faena</th>
                            <th class="p-3">Peso Vivo</th>
                            <th class="p-3">Canal Caliente (PCC)</th>
                            <th class="p-3">Rend. %</th>
                            <th class="p-3">pH 24h</th>
                            <th class="p-3">Marmoleo</th>
                            <th class="p-3">Clasificación NMX</th>
                            <th class="p-3">Valor Total (MXN)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($evaluaciones as $eval)
                            @php
                                $diagPh = $eval->diagnostico_ph;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-mono font-bold text-slate-800">{{ $eval->folio_rastro_canal }}</td>
                                <td class="p-3 font-mono text-slate-700">
                                    @if($eval->ganado)
                                        <a href="{{ route('compras.ganado.perfil', ['arete' => $eval->arete_snapshot]) }}" class="hover:text-emerald-600 underline">
                                            {{ $eval->arete_snapshot }}
                                        </a>
                                    @else
                                        {{ $eval->arete_snapshot }}
                                    @endif
                                </td>
                                <td class="p-3 text-slate-600">{{ $eval->fecha_faena->format('d/m/Y') }}</td>
                                <td class="p-3 font-mono text-slate-700">{{ number_format($eval->peso_vivo_bascula_kg, 1) }} kg</td>
                                <td class="p-3 font-mono font-bold text-slate-900">{{ number_format($eval->peso_canal_caliente_kg, 1) }} kg</td>
                                <td class="p-3 font-mono font-bold text-emerald-700">{{ number_format($eval->rendimiento_canal_caliente_pct, 2) }}%</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $diagPh['color_badge'] }}">
                                        {{ number_format($eval->ph_24h, 2) }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-700 font-semibold">{{ $eval->marmoleo }}</td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $eval->clasificacion_mexicana === 'Suprema' ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($eval->clasificacion_mexicana === 'Selecta' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $eval->clasificacion_mexicana }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono font-black text-emerald-700">
                                    {{ formatoPesos($eval->valor_total_canal_mxn) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="p-8 text-center text-slate-400 italic">
                                    No hay evaluaciones de canal registradas aún. Registre el reporte de faena del rastro haciendo clic en "Registrar Evaluación de Canal".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($evaluaciones->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $evaluaciones->links() }}
                </div>
            @endif
        </div>

        <!-- ================= MODAL: NUEVA EVALUACIÓN DE CANAL ================= -->
        <div x-show="modalCanalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalCanalOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-drumstick-bite text-rose-600"></i> Registrar Datos de Sacrificio y Canal
                    </h3>
                    <button @click="modalCanalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('calidad.carne.store') }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Folio Canal / Rastro *</label>
                            <input type="text" name="folio_rastro_canal" required placeholder="Ej. CANAL-2026-001"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono uppercase">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha de Faena *</label>
                            <input type="date" name="fecha_faena" value="{{ date('Y-m-d') }}" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Arete SINIIGA / Animal *</label>
                            <input type="text" name="arete_snapshot" required placeholder="Arete del animal sacrificado"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Planta de Sacrificio / Rastro TIF</label>
                            <input type="text" name="planta_o_rastro" placeholder="Ej. Rastro Frigorífico TIF 148"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                        </div>
                    </div>

                    <!-- Pesos y Rendimiento -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Peso Vivo Báscula (kg) *</label>
                            <input type="number" step="0.1" name="peso_vivo_bascula_kg" placeholder="Ej. 490.0" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Canal Caliente PCC (kg) *</label>
                            <input type="number" step="0.1" name="peso_canal_caliente_kg" placeholder="Ej. 295.0" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Merma Oreo Cámara (%)</label>
                            <input type="number" step="0.1" name="merma_enfriamiento_pct" value="2.0"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                        </div>
                    </div>

                    <!-- Parámetros Sensoriales Dra. Rubio -->
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">pH a las 24 horas *</label>
                            <input type="number" step="0.01" name="ph_24h" value="5.55" min="4.8" max="7.2" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                            <span class="text-[10px] text-slate-400">Ideal: 5.40 - 5.75</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Grado de Marmoleo *</label>
                            <select name="marmoleo" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                                <option value="Abundante">Abundante (Prime)</option>
                                <option value="Moderado" selected>Moderado (Choice High)</option>
                                <option value="Pequeno">Pequeño (Choice)</option>
                                <option value="Ligero">Ligero (Select)</option>
                                <option value="Practicamente_Carente">Prácticamente Carente (Standard)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Espesor Grasa Dorsal (mm) *</label>
                            <input type="number" step="0.1" name="espesor_grasa_dorsal_mm" value="6.5" min="1" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Color de Carne *</label>
                            <select name="color_carne" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                                <option value="Rojo_Cereza_Brillante" selected>Rojo Cereza Brillante (Óptimo)</option>
                                <option value="Rojo_Medio">Rojo Medio</option>
                                <option value="Rojo_Oscuro_DFD">Rojo Oscuro / DFD (Estrés pre-faena)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Color de Grasa *</label>
                            <select name="color_grasa" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                                <option value="Blanco_Nieve">Blanco Nieve</option>
                                <option value="Blanco_Cremoso" selected>Blanco Cremoso (Grano)</option>
                                <option value="Ligeramente_Amarillo">Ligeramente Amarillo</option>
                                <option value="Amarillo">Amarillo (Pastoreo intenso)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Clasificación Norma Mexicana *</label>
                            <select name="clasificacion_mexicana" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500">
                                <option value="Suprema">Suprema</option>
                                <option value="Selecta" selected>Selecta</option>
                                <option value="Estandar">Estándar</option>
                                <option value="Comercial">Comercial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Precio Canal ($ MXN/kg) *</label>
                            <input type="number" step="0.5" name="precio_canal_por_kg_mxn" value="115.0" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Observaciones Técnicas</label>
                        <textarea name="observaciones_tecnicas" rows="2" placeholder="Tiempo de transporte, ayuno en corral de espera, terneza..."
                                  class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-rose-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalCanalOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Guardar Evaluación
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
