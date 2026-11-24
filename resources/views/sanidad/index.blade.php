<x-app-layout>
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{ modalTratamientoOpen: false }">

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
                    <span class="w-9 h-9 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-notes-medical"></i>
                    </span>
                    Sanidad, Bioseguridad y Control de Retiro
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Protocolos de recepción, vacunación, implantes promotores y control de días de retiro para rastro.
                </p>
            </div>
            <button @click="modalTratamientoOpen = true"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                <i class="fa-solid fa-plus"></i> Aplicar Tratamiento
            </button>
        </div>

        <!-- 1. ALERTAS DE PERIODO DE RETIRO VETERINARIO -->
        @if($animalesEnRetiro->count() > 0)
            <div class="p-5 bg-gradient-to-r from-rose-500 to-rose-600 rounded-2xl text-white shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm sm:text-base">¡Atención Sanitaria: {{ $animalesEnRetiro->count() }} Animales en Periodo de Retiro!</h4>
                        <p class="text-xs text-rose-100">
                            Estos bovinos tienen medicamentos activos. <strong>NO deben enviarse a rastro ni comercializarse</strong> hasta cumplir sus días reglamentarios.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('sanidad.index', ['filtro' => 'en_retiro']) }}"
                       class="bg-white hover:bg-slate-50 text-rose-900 px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-xs">
                        Filtrar Animales en Retiro
                    </a>
                </div>
            </div>
        @endif

        <!-- 2. KPIs SANITARIOS EN PESOS MEXICANOS (MXN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Animales en Retiro Activo -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">En Periodo de Retiro</p>
                    <h3 class="text-2xl font-black {{ $animalesEnRetiro->count() > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                        {{ $animalesEnRetiro->count() }} <span class="text-sm font-medium text-slate-500">Cabezas</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bloqueados para faena</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-ban"></i>
                </div>
            </div>

            <!-- Inversión Sanitaria MXN -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Inversión Médica (MXN)</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-700 font-mono mt-1">
                        {{ formatoPesos($inversionSanitariaMXN) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Vacunas, biológicos y desparasitantes</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-syringe"></i>
                </div>
            </div>

            <!-- Total Eventos Sanitarios -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Aplicaciones Registradas</p>
                    <h3 class="text-2xl font-black text-slate-800 font-mono mt-1">
                        {{ number_format($totalTratamientos) }} <span class="text-sm font-medium text-slate-500">Dosis</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Historial acumulado</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>

            <!-- Protocolo Recepción Completo -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Protocolo de Entrada</p>
                    <h3 class="text-2xl font-black text-teal-700 mt-1">
                        Activo
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Bacterina + Ivermectina + Implante</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-shield-virus"></i>
                </div>
            </div>
        </div>

        <!-- 3. PROTOCOLO TÉCNICO DE RECEPCIÓN (ENGORMIX / MÉXICO) -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-3">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-teal-600"></i> Protocolo Oficial de Llegada y Cuarentena para Ganado de Compra
            </h3>
            <p class="text-xs text-slate-500">
                Al comprar ganado de engorda (becerros o toretes de repasto), el estrés de transporte y la merma provocan inmunodepresión. Se recomienda aplicar el protocolo en las primeras 24-48 horas:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2 text-xs">
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center text-[10px]">1</span>
                        Desparasitación Interna/Externa
                    </div>
                    <p class="text-slate-500 text-[11px]">Ivermectina al 3.15% o Levamisol para erradicar parásitos gastrointestinales y garrapatas.</p>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center text-[10px]">2</span>
                        Bacterina Clostridial & Respiratoria
                    </div>
                    <p class="text-slate-500 text-[11px]">Vacuna 8 a 10 vías contra Clostridiosis y complejo respiratorio bovino (BRD / neumonías).</p>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center text-[10px]">3</span>
                        Vitaminas A, D, E y Fósforo
                    </div>
                    <p class="text-slate-500 text-[11px]">Complejo reconstituyente metabólico inyectable para rápida recuperación del tejido muscular.</p>
                </div>

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                        <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center text-[10px]">4</span>
                        Implante Hormonal Promotor
                    </div>
                    <p class="text-slate-500 text-[11px]">Implante auricular (TBA + Estradiol) para mejorar la ganancia diaria de peso (GDP) hasta un 15%.</p>
                </div>
            </div>
        </div>

        <!-- 4. TABLA DE TRATAMIENTOS Y MEDICAMENTOS REGISTRADOS -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-notes-medical text-teal-600"></i> Bitácora Sanitaria y Medicamentos Aplicados
                    </h3>
                    <p class="text-xs text-slate-500">Historial por arete con semáforo de tiempo de retiro rastro.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('sanidad.index', ['filtro' => 'todos']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filtro === 'todos' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Todos
                    </a>
                    <a href="{{ route('sanidad.index', ['filtro' => 'en_retiro']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filtro === 'en_retiro' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                        En Retiro ({{ $animalesEnRetiro->count() }})
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                            <th class="p-3">Arete ID</th>
                            <th class="p-3">Fecha Aplicación</th>
                            <th class="p-3">Tipo Evento</th>
                            <th class="p-3">Producto / Medicamento</th>
                            <th class="p-3">Dosis / Vía</th>
                            <th class="p-3">Días Retiro</th>
                            <th class="p-3">Fin de Retiro / Alerta</th>
                            <th class="p-3">Costo Dosis (MXN)</th>
                            <th class="p-3">Veterinario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tratamientos as $t)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-mono font-bold text-slate-800">
                                    <a href="{{ route('compras.ganado.perfil', ['arete' => $t->ganado->areteID]) }}" class="hover:text-teal-600 underline">
                                        {{ $t->ganado->areteID }}
                                    </a>
                                </td>
                                <td class="p-3 text-slate-600">{{ $t->fecha_aplicacion->format('d/m/Y') }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $t->tipo_evento }}
                                    </span>
                                </td>
                                <td class="p-3 font-semibold text-slate-800">{{ $t->medicamento_producto }}</td>
                                <td class="p-3 text-slate-600">{{ $t->dosis_aplicada }} ({{ $t->via_administracion }})</td>
                                <td class="p-3 font-mono text-slate-700">{{ $t->dias_retiro_rastro }} días</td>
                                <td class="p-3">
                                    @if($t->esta_en_retiro)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 animate-pulse flex items-center gap-1 w-max">
                                            <i class="fa-solid fa-clock"></i> Faltan {{ $t->dias_restantes_retiro }} días ({{ $t->fecha_fin_retiro->format('d/m') }})
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1 w-max">
                                            <i class="fa-solid fa-check"></i> Cumplido / Apto
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 font-mono font-bold text-emerald-700">
                                    {{ formatoPesos($t->costo_medicamento_mxn) }}
                                </td>
                                <td class="p-3 text-slate-500">{{ $t->veterinario_responsable ?? 'N/D' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-400 italic">
                                    No hay tratamientos registrados en esta vista.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tratamientos->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $tratamientos->links() }}
                </div>
            @endif
        </div>

        <!-- ================= MODAL: NUEVO TRATAMIENTO ================= -->
        <div x-show="modalTratamientoOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalTratamientoOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-syringe text-teal-600"></i> Registrar Aplicación de Medicamento / Vacuna
                    </h3>
                    <button @click="modalTratamientoOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('sanidad.store') }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Animal (Arete ID) *</label>
                        <select name="ganado_id" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                            <option value="">-- Seleccionar Arete --</option>
                            @foreach($ganadosActivos as $bovino)
                                <option value="{{ $bovino->GanadoID }}">
                                    {{ $bovino->areteID }} - {{ $bovino->categoria }} ({{ $bovino->ultimoPeso }} kg)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipo de Evento *</label>
                            <select name="tipo_evento" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500">
                                <option value="Recepcion">Recepción de Llegada</option>
                                <option value="Vacunacion">Vacunación</option>
                                <option value="Desparasitacion">Desparasitación</option>
                                <option value="Implante">Implante Promotor</option>
                                <option value="Vitamina">Vitaminas / Fósforo</option>
                                <option value="Antibiotico">Antibiótico / Terapéutico</option>
                                <option value="Tratamiento_Clinico">Tratamiento Clínico</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha de Aplicación *</label>
                            <input type="date" name="fecha_aplicacion" value="{{ date('Y-m-d') }}" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Producto / Medicamento Comercial *</label>
                        <input type="text" name="medicamento_producto" required placeholder="Ej. Bovilis Vista Once, Dectomax, Revalor-S..."
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Dosis Aplicada *</label>
                            <input type="text" name="dosis_aplicada" required placeholder="Ej. 5 ml, 1 implante"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Vía de Administración *</label>
                            <select name="via_administracion" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500">
                                <option value="Subcutánea" selected>Subcutánea (SQ)</option>
                                <option value="Intramuscular">Intramuscular (IM)</option>
                                <option value="Oral">Oral</option>
                                <option value="Tópica / Pour-on">Tópica / Pour-on</option>
                                <option value="Implante en Oreja">Implante en Oreja</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Días de Retiro para Rastro *</label>
                            <input type="number" name="dias_retiro_rastro" value="0" min="0" max="120" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                            <span class="text-[10px] text-slate-400">0 si no requiere tiempo de espera</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Costo Dosis ($ MXN) *</label>
                            <input type="number" step="0.5" name="costo_medicamento_mxn" value="65.0" min="0" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Veterinario Responsable</label>
                        <input type="text" name="veterinario_responsable" placeholder="Dr. / MVZ..."
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Diagnóstico / Motivo</label>
                        <textarea name="diagnostico_motivo" rows="2" placeholder="Observaciones de la aplicación..."
                                  class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalTratamientoOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Guardar Tratamiento
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
