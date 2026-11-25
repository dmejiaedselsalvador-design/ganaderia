<x-app-layout>
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="{ modalAsignarOpen: false, modalConsumoOpen: false }">

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

        <!-- ENCABEZADO Y ACCIONES -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
            <div class="flex items-center gap-3.5">
                <a href="{{ route('corrales.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-800">
                            {{ $corral->codigo }}
                        </span>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $corral->estado === 'Activo' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ $corral->estado }}
                        </span>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-lg bg-amber-50 text-amber-800 border border-amber-200">
                            Fase: {{ $corral->fase_engorda }}
                        </span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900 mt-1">{{ $corral->nombre }}</h2>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                <button @click="modalAsignarOpen = true"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-plus"></i> + Asignar Ganado
                </button>
                <button @click="modalConsumoOpen = true"
                        class="bg-amber-500 hover:bg-amber-600 text-white px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-utensils"></i> Servir Alimento
                </button>
                @can('ganaderia.nutricion')
                    <a href="{{ route('nutricion.plan.corral', $corral) }}"
                       class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-diagram-project"></i> Dieta y consumo
                    </a>
                @endcan
            </div>
        </div>

        <!-- TARJETAS DE INDICADORES ZOOTÉCNICOS DEL LOTE -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-400 block uppercase">Ocupación Actual</span>
                <div class="text-2xl font-black text-slate-800 mt-1">
                    {{ $corral->total_cabezas }} <span class="text-xs font-normal text-slate-400">/ {{ $corral->capacidad_maxima }}</span>
                </div>
                <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, $corral->porcentaje_ocupacion) }}%"></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-400 block uppercase">Peso Promedio Lote</span>
                <div class="text-2xl font-black text-slate-800 font-mono mt-1">
                    {{ number_format($corral->peso_promedio_actual, 1) }} <span class="text-xs font-normal text-slate-400">kg</span>
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Meta: {{ number_format($corral->peso_promedio_objetivo, 0) }} kg</span>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-400 block uppercase">GDP Media del Lote</span>
                <div class="text-2xl font-black text-emerald-700 font-mono mt-1">
                    {{ number_format($corral->gdp_promedio_lote, 3) }} <span class="text-xs font-normal text-slate-400">kg/d</span>
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">~{{ $corral->dias_promedio_en_engorda }} días en corral</span>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-400 block uppercase">Inversión Acumulada</span>
                <div class="text-xl sm:text-2xl font-black text-emerald-700 font-mono mt-1">
                    {{ formatoPesos($corral->inversion_total_corral_mxn) }}
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Ganado + Alimento</span>
            </div>
        </div>

        <!-- 1. GANADO EN ESTE CORRAL -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-cow text-emerald-600"></i> Inventario de Bovinos en el Corral ({{ $corral->ganadosActivos->count() }})
                    </h3>
                    <p class="text-xs text-slate-500">Monitoreo individual de ganancia diaria de peso y meta de faena.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                            <th class="p-3">Arete ID</th>
                            <th class="p-3">Raza / Categoría</th>
                            <th class="p-3">Peso Ingreso</th>
                            <th class="p-3">Último Peso</th>
                            <th class="p-3">Δ Ganancia</th>
                            <th class="p-3">GDP (kg/d)</th>
                            <th class="p-3">Costo Compra</th>
                            <th class="p-3">Meta Venta</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($corral->ganadosActivos as $ganado)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-mono font-bold text-slate-800">
                                    <a href="{{ route('compras.ganado.perfil', ['arete' => $ganado->areteID]) }}" class="hover:text-emerald-600 underline">
                                        {{ $ganado->areteID }}
                                    </a>
                                </td>
                                <td class="p-3 text-slate-700">
                                    <span class="font-semibold">{{ $ganado->categoria }}</span>
                                    <span class="text-[11px] text-slate-400 block">{{ $ganado->raza ?: 'Cruzado' }}</span>
                                </td>
                                <td class="p-3 font-mono text-slate-600">
                                    {{ number_format($ganado->pesoActual ?? $ganado->ultimoPeso, 1) }} kg
                                </td>
                                <td class="p-3 font-mono font-bold text-slate-900">
                                    {{ number_format($ganado->ultimoPeso, 1) }} kg
                                </td>
                                <td class="p-3 font-mono font-bold text-emerald-600">
                                    +{{ number_format($ganado->ganancia_total, 1) }} kg
                                </td>
                                <td class="p-3 font-mono font-bold text-emerald-700">
                                    {{ number_format($ganado->gdp_historica, 3) }}
                                </td>
                                <td class="p-3 font-mono text-slate-700">
                                    {{ formatoPesos($ganado->precioGanadoTotal) }}
                                </td>
                                <td class="p-3">
                                    @if($ganado->esta_listo_venta)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            🎯 Listo
                                        </span>
                                    @else
                                        <span class="text-[11px] text-slate-500 font-mono">
                                            Faltan {{ max(0, round($ganado->peso_objetivo_calculado - $ganado->ultimoPeso, 1)) }} kg
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <form action="{{ route('corrales.remover.ganado', ['corralId' => $corral->id, 'ganadoId' => $ganado->GanadoID]) }}" method="POST" data-confirm="¿Sacar al animal {{ $ganado->areteID }} de este corral?" data-titulo="Sacar del corral" data-boton="Sí, sacar">
                                        @csrf
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 transition" title="Sacar del corral">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sacar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-400 italic">
                                    No hay ganado asignado a este corral actualmente. Utilice el botón "+ Asignar Ganado" arriba.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. BITÁCORA DE CONSUMOS DE ALIMENTO EN EL CORRAL -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-wheat-awn text-amber-600"></i> Bitácora de Alimento Servido (MXN)
                    </h3>
                    <p class="text-xs text-slate-500">Historial de raciones, kilos consumidos y costo total por servida.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-100">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                            <th class="p-3">Fecha</th>
                            <th class="p-3">Dieta / Ración</th>
                            <th class="p-3">Kg Servidos</th>
                            <th class="p-3">Kg Rechazo</th>
                            <th class="p-3">Kg Consumidos</th>
                            <th class="p-3">Kg / Cabeza</th>
                            <th class="p-3">Costo Total (MXN)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($corral->consumos as $consumo)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-3 font-semibold text-slate-800">
                                    {{ $consumo->fecha->format('d/m/Y') }}
                                </td>
                                <td class="p-3 font-semibold text-slate-700">
                                    {{ $consumo->racion->nombre }}
                                </td>
                                <td class="p-3 font-mono text-slate-700">
                                    {{ number_format($consumo->kilos_servidos, 1) }} kg
                                </td>
                                <td class="p-3 font-mono text-slate-400">
                                    {{ number_format($consumo->kilos_rechazo, 1) }} kg
                                </td>
                                <td class="p-3 font-mono font-bold text-slate-900">
                                    {{ number_format($consumo->kilos_consumidos, 1) }} kg
                                </td>
                                <td class="p-3 font-mono text-slate-700">
                                    {{ number_format($consumo->consumo_promedio_cabeza_kg, 2) }} kg
                                </td>
                                <td class="p-3 font-mono font-bold text-emerald-700">
                                    {{ formatoPesos($consumo->costo_total_mxn) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-slate-400 italic">
                                    Aún no se han registrado consumos de ración para este corral.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= MODAL: ASIGNAR GANADO AL CORRAL ================= -->
        <div x-show="modalAsignarOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalAsignarOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-cow text-emerald-600"></i> Asignar Ganado a {{ $corral->nombre }}
                    </h3>
                    <button @click="modalAsignarOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('corrales.asignar.ganado', $corral->id) }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf
                    <p class="text-xs text-slate-500">
                        Seleccione los animales activos en el rancho que desea trasladar a este corral (Capacidad disponible: {{ max(0, $corral->capacidad_maxima - $corral->total_cabezas) }} cabezas).
                    </p>

                    <div class="max-h-72 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 p-2">
                        @forelse($ganadoDisponible as $animal)
                            <label class="flex items-center gap-3 p-2 hover:bg-slate-50 rounded-lg cursor-pointer">
                                <input type="checkbox" name="ganado_ids[]" value="{{ $animal->GanadoID }}"
                                       class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                <div class="flex-1 flex justify-between items-center text-xs">
                                    <span class="font-mono font-bold text-slate-800">{{ $animal->areteID }}</span>
                                    <span class="text-slate-500">{{ $animal->categoria }} · {{ $animal->raza ?: 'Cruzado' }}</span>
                                    <span class="font-mono font-semibold text-slate-700">{{ number_format($animal->ultimoPeso, 1) }} kg</span>
                                    <span class="font-mono text-emerald-700">{{ formatoPesos($animal->precioGanadoTotal) }}</span>
                                </div>
                            </label>
                        @empty
                            <div class="p-6 text-center text-slate-400 italic">
                                No hay animales libres sin corral asignado. Ingrese ganado nuevo o remueva de otros corrales.
                            </div>
                        @endforelse
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalAsignarOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Confirmar Asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= MODAL: SERVIR ALIMENTO ================= -->
        <div x-show="modalConsumoOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div @click.away="modalConsumoOpen = false"
                 class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-utensils text-amber-600"></i> Registrar Servido de Ración
                    </h3>
                    <button @click="modalConsumoOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('nutricion.consumo.store') }}" method="POST" class="p-5 space-y-4 text-xs sm:text-sm">
                    @csrf
                    <input type="hidden" name="corral_lote_id" value="{{ $corral->id }}">

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Dieta / Ración a Servir *</label>
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
                            <input type="number" step="0.1" name="kilos_servidos" placeholder="Ej. 350.0" required
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kilos de Rechazo (Sobrante)</label>
                        <input type="number" step="0.1" name="kilos_rechazo" value="0.0"
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono">
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Alimento no consumido en comederos</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Turno de la mañana, clima, lectura comederos..."
                               class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalConsumoOpen = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-medium transition">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-semibold transition shadow-xs">
                            Registrar Consumo
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
