<x-app-layout>
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ modalCorralOpen: false }">

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
                    <span class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-warehouse"></i>
                    </span>
                    Corrales y Lotes de Engorda (Feedlot)
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Gestión por lotes homogéneos, fases nutricionales, capacidad instalada y seguimiento zootécnico.
                </p>
            </div>
            <button @click="modalCorralOpen = true"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-plus"></i> Nuevo Corral / Lote
            </button>
        </div>

        <!-- 1. KPIs GLOBALES DE CORRALES EN MONEDA NACIONAL (MXN) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Cabezas en Engorda -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ganado en Corrales</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">
                        {{ number_format($totalCabezasEngorda) }} <span class="text-sm font-medium text-slate-500">Cabezas</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">En engorda intensiva</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cow"></i>
                </div>
            </div>

            <!-- Ocupación Instalada -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ocupación Corrales</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">
                        {{ $porcentajeOcupacionGlobal }}%
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">De {{ $capacidadTotalInstalada }} plazas totales</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>

            <!-- Total Inversión en Ganado MXN -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Inversión en Pie (MXN)</p>
                    <h3 class="text-xl sm:text-2xl font-black text-emerald-700 font-mono mt-1">
                        {{ formatoPesos($inversionTotalEngordaMXN) }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Capital en ganado activo</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>

            <!-- Lotes Activos -->
            <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Corrales Operativos</p>
                    <h3 class="text-2xl font-black text-indigo-700 mt-1">
                        {{ $corrales->where('estado', 'Activo')->count() }} <span class="text-sm font-medium text-slate-500">Lotes</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $corrales->count() }} corrales totales</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
        </div>

        <!-- 2. TARJETAS DE CORRALES Y LOTES -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800">Directorio de Corrales</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('corrales.index', ['filtro' => 'todos']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filtro === 'todos' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        Todos ({{ $corrales->count() }})
                    </a>
                    <a href="{{ route('corrales.index', ['filtro' => 'Activo']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filtro === 'Activo' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        Activos
                    </a>
                    <a href="{{ route('corrales.index', ['filtro' => 'Vacio']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $filtro === 'Vacio' ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
                        Vacíos
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($corrales as $corral)
                    @php
                        $ocupacion = $corral->porcentaje_ocupacion;
                        $colorOcupacion = $ocupacion >= 90 ? 'bg-rose-500' : ($ocupacion >= 70 ? 'bg-amber-500' : 'bg-emerald-500');
                    @endphp
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 hover:border-slate-300 transition-all p-5 flex flex-col justify-between">
                        <div>
                            <!-- Header de la tarjeta -->
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                            {{ $corral->codigo }}
                                        </span>
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full {{ $corral->estado === 'Activo' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $corral->estado }}
                                        </span>
                                    </div>
                                    <h4 class="font-extrabold text-slate-800 text-base mt-1.5">{{ $corral->nombre }}</h4>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $corral->fase_engorda === 'Finalizacion' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($corral->fase_engorda === 'Crecimiento' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') }}">
                                    {{ $corral->fase_engorda }}
                                </span>
                            </div>

                            <!-- Barra de ocupación -->
                            <div class="mt-4 space-y-1">
                                <div class="flex justify-between text-xs font-medium text-slate-600">
                                    <span>Ocupación de Corral</span>
                                    <span><strong>{{ $corral->total_cabezas }}</strong> / {{ $corral->capacidad_maxima }} cabezas ({{ $ocupacion }}%)</span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $colorOcupacion }} rounded-full transition-all" style="width: {{ min(100, $ocupacion) }}%"></div>
                                </div>
                            </div>

                            <!-- Estadísticas Técnicas del Lote -->
                            <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-slate-100 text-xs">
                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Peso Promedio</span>
                                    <span class="text-sm font-extrabold text-slate-800 font-mono">
                                        {{ number_format($corral->peso_promedio_actual, 1) }} kg
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Meta: {{ number_format($corral->peso_promedio_objetivo, 0) }} kg</span>
                                </div>

                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">GDP Promedio</span>
                                    <span class="text-sm font-extrabold text-emerald-700 font-mono">
                                        {{ number_format($corral->gdp_promedio_lote, 3) }} kg/d
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Días: ~{{ $corral->dias_promedio_en_engorda }}</span>
                                </div>

                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Inversión Ganado</span>
                                    <span class="text-xs font-bold text-slate-700 font-mono block truncate">
                                        {{ formatoPesos($corral->inversion_compra_total_mxn) }}
                                    </span>
                                </div>

                                <div class="bg-slate-50 p-2.5 rounded-xl">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Alimento Servido</span>
                                    <span class="text-xs font-bold text-amber-700 font-mono block truncate">
                                        {{ formatoPesos($corral->costo_total_alimento_mxn) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer con Botón Ver Corral -->
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">
                                @if($corral->fecha_proyectada_salida)
                                    Salida: {{ $corral->fecha_proyectada_salida->format('d/m/Y') }}
                                @else
                                    Sin fecha de salida fija
                                @endif
                            </span>
                            <a href="{{ route('corrales.show', $corral->id) }}"
                               class="bg-slate-900 hover:bg-emerald-600 text-white px-3.5 py-1.5 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                                Administrar <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-slate-200 p-8 space-y-3">
                        <i class="fa-solid fa-warehouse text-4xl text-slate-300"></i>
                        <h4 class="font-bold text-slate-700 text-base">No hay corrales registrados en este momento</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">
                            Comience creando su primer corral de engorda para clasificar su ganado por lotes homogéneos y registrar consumos de ración.
                        </p>
                        <button @click="modalCorralOpen = true" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold">
                            + Crear Primer Corral
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ================= MODAL: NUEVO CORRAL ================= -->
        <div x-show="modalCorralOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalCorralOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-warehouse text-emerald-600"></i> Alta de Nuevo Corral / Lote
                    </h3>
                    <button @click="modalCorralOpen = false" class="text-slate-400 hover:text-slate-600 text-sm">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('corrales.store') }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Código del Corral *</label>
                            <input type="text" name="codigo" required placeholder="Ej. COR-01, LOTE-NORTE"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono uppercase">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fase de Engorda *</label>
                            <select name="fase_engorda" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                                <option value="Recepcion">1. Recepción y Adaptación</option>
                                <option value="Crecimiento" selected>2. Crecimiento / Desarrollo</option>
                                <option value="Finalizacion">3. Finalización Intensiva</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nombre Descriptivo *</label>
                        <input type="text" name="nombre" required placeholder="Ej. Corral 1 - Novillos Suizo Europeo x Cebú"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Capacidad Máxima (Cabezas) *</label>
                            <input type="number" name="capacidad_maxima" value="30" min="1" max="500" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Peso Meta Venta (kg) *</label>
                            <input type="number" step="0.1" name="peso_promedio_objetivo" value="480.0" min="200" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha Inicio del Lote</label>
                            <input type="date" name="fecha_inicio_lote" value="{{ date('Y-m-d') }}"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Fecha Proyectada Salida</label>
                            <input type="date" name="fecha_proyectada_salida"
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estado Inicial *</label>
                        <select name="estado" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                            <option value="Activo" selected>Activo (En engorda)</option>
                            <option value="Vacio">Vacío / Limpieza</option>
                            <option value="En Cuarentena">En Cuarentena Sanitaria</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Notas u Observaciones</label>
                        <textarea name="observaciones" rows="2" placeholder="Detalles de bebederos, comederos o tipo de cruza..."
                                  class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalCorralOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Guardar Corral
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
