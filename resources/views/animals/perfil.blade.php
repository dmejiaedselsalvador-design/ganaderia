<x-app-layout>
    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-4 sm:py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ modalPesajeOpen: false, modalBajaOpen: false }">

        <!-- 1. BARRA DE BÚSQUEDA Y ACCESO RÁPIDO -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-4 sm:p-5 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg shrink-0">
                    <i class="fa-solid fa-cow"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 text-base sm:text-lg">Expediente Operativo del Bovino</h2>
                    <p class="text-xs text-slate-500">Historial biométrico, báscula, ganancia de peso (GDP) y estado vital.</p>
                </div>
            </div>

            <!-- Buscador optimizado -->
            <form action="{{ route('compras.ganado.perfil') }}" method="GET" class="flex items-center gap-2 w-full md:w-96">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="arete" value="{{ request('arete') }}" placeholder="Escanear o digitar Arete SINIIGA..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition font-mono">
                </div>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shrink-0 shadow-xs">
                    Buscar
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-center gap-3 text-emerald-800 text-sm shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-sm shadow-xs">
                <div class="font-bold flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Error en la operación
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($ganadoSeleccionado))

            <!-- 2. BANNERS DE ALERTA OPERATIVA -->
            @if($ganadoSeleccionado->esta_listo_venta)
                <div class="p-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-2xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
                            🎯
                        </div>
                        <div>
                            <h3 class="font-bold text-sm sm:text-base">¡Animal Listo para Comercialización!</h3>
                            <p class="text-xs text-amber-100">
                                Ha alcanzado <strong class="text-white">{{ number_format($ganadoSeleccionado->ultimoPeso, 2) }} kg</strong>, superando el peso objetivo de <strong class="text-white">{{ number_format($ganadoSeleccionado->peso_objetivo_calculado, 1) }} kg</strong> para su categoría.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('ventas.create', ['seleccionados' => $ganadoSeleccionado->GanadoID]) }}"
                       class="bg-white hover:bg-slate-50 text-amber-900 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm shrink-0 flex items-center gap-1.5">
                        <i class="fa-solid fa-cart-shopping"></i> Iniciar Venta
                    </a>
                </div>
            @endif

            @if($ganadoSeleccionado->isMuerto())
                <div class="p-4 bg-rose-900 text-white rounded-2xl shadow-sm flex items-start gap-3 border border-rose-800">
                    <div class="w-10 h-10 rounded-xl bg-rose-800/80 flex items-center justify-center text-lg shrink-0 text-rose-300">
                        <i class="fa-solid fa-skull"></i>
                    </div>
                    <div class="space-y-0.5 text-xs">
                        <h3 class="font-bold text-sm text-rose-200 uppercase tracking-wide">Registro de Defunción / Baja Médica</h3>
                        <p class="text-rose-100">
                            Fallecido el <strong>{{ $ganadoSeleccionado->baja?->fecha_muerte?->format('d/m/Y') ?? 'N/D' }}</strong>.
                            Causa: <strong class="text-white">{{ $ganadoSeleccionado->baja?->causa_muerte ?? 'No especificada' }}</strong>.
                        </p>
                        @if($ganadoSeleccionado->baja?->necropsia)
                            <p class="text-rose-300 text-[11px] italic mt-1">
                                Necropsia: "{{ $ganadoSeleccionado->baja->necropsia }}"
                            </p>
                        @endif
                        <p class="text-rose-400 text-[10px] uppercase font-bold pt-1">
                            Operaciones bloqueadas: El animal no puede ser pesado ni vendido.
                        </p>
                    </div>
                </div>
            @endif

            @if($ganadoSeleccionado->isVendido())
                <div class="p-4 bg-blue-900 text-white rounded-2xl shadow-sm flex items-start gap-3 border border-blue-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-800/80 flex items-center justify-center text-lg shrink-0 text-blue-300">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <div class="space-y-0.5 text-xs">
                        <h3 class="font-bold text-sm text-blue-200 uppercase tracking-wide">Animal Vendido / Descontado de Hato</h3>
                        <p class="text-blue-100">
                            Comercializado en Folio <strong>{{ $ganadoSeleccionado->detalleVenta?->venta?->folio_venta ?? 'N/D' }}</strong> el día <strong>{{ $ganadoSeleccionado->detalleVenta?->venta?->fecha_venta?->format('d/m/Y') ?? 'N/D' }}</strong>.
                            Cliente: <strong class="text-white">{{ $ganadoSeleccionado->detalleVenta?->venta?->cliente_nombre ?? 'N/D' }}</strong>.
                        </p>
                        <p class="text-blue-300 font-mono text-[11px]">
                            Precio de salida: {{ formatoPesos($ganadoSeleccionado->detalleVenta?->subtotal ?? 0) }} ({{ number_format($ganadoSeleccionado->detalleVenta?->peso_venta ?? 0, 2) }} kg)
                        </p>
                    </div>
                </div>
            @endif

            <!-- 3. TARJETA DE RESUMEN DE IDENTIDAD Y ACCIONES -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 sm:p-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">

                    <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                        <div class="px-5 py-3.5 bg-emerald-50 border border-emerald-100 rounded-2xl text-center">
                            <span class="text-[10px] uppercase font-bold text-emerald-600 block tracking-wider">Arete ID</span>
                            <span class="text-2xl font-black text-emerald-950 font-mono">{{ $ganadoSeleccionado->areteID }}</span>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 text-lg">
                                    {{ $ganadoSeleccionado->categoria }} · {{ $ganadoSeleccionado->raza ?? 'Cruzado / General' }}
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $ganadoSeleccionado->status === 'Activo' ? 'bg-emerald-100 text-emerald-800' : ($ganadoSeleccionado->status === 'Vendido' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $ganadoSeleccionado->status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 flex flex-wrap gap-x-4 gap-y-1">
                                <span>Sexo: <strong class="text-slate-700">{{ $ganadoSeleccionado->sexo }}</strong></span>
                                <span>Corral: <strong class="text-slate-700">{{ $ganadoSeleccionado->lote_potrero ?: 'General' }}</strong></span>
                                <span>Proveedor: <strong class="text-slate-700">{{ $ganadoSeleccionado->factura?->proveedor?->nombreContacto ?? 'Sin proveedor' }}</strong></span>
                                <span>Costo Adquisición: <strong class="text-slate-700 font-mono">{{ formatoPesos($ganadoSeleccionado->precioGanadoTotal) }}</strong></span>
                            </p>
                        </div>
                    </div>

                    <!-- Botones de Acción Táctil (Habilitados sólo si puede operar) -->
                    <div class="flex items-center gap-2.5 w-full lg:w-auto">
                        @if($ganadoSeleccionado->puedeOperar())
                            <button @click="modalPesajeOpen = true"
                                    class="flex-1 lg:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
                                <i class="fa-solid fa-weight-scale"></i> + Nuevo Pesaje
                            </button>

                            <a href="{{ route('ventas.create', ['seleccionados' => $ganadoSeleccionado->GanadoID]) }}"
                               class="flex-1 lg:flex-none bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
                                <i class="fa-solid fa-tag"></i> Vender
                            </a>

                            <button @click="modalBajaOpen = true"
                                    class="bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-1.5 border border-slate-200">
                                <i class="fa-solid fa-skull"></i> Registrar Baja
                            </button>
                        @else
                            <span class="text-xs text-slate-400 font-semibold px-4 py-2 bg-slate-100 rounded-xl">
                                Expediente cerrado ({{ $ganadoSeleccionado->status }})
                            </span>
                        @endif
                    </div>

                </div>
            </div>

            <!-- 4. MÉTRICAS OPERATIVAS DEL ANIMAL (GDP / PESOS) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Último Peso -->
                <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase">Último Peso Báscula</span>
                    <div class="text-2xl font-black text-slate-800 mt-1">
                        {{ number_format($ganadoSeleccionado->ultimoPeso, 2) }} <span class="text-xs font-normal text-slate-500">kg</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">
                        Peso ingreso: {{ number_format($ganadoSeleccionado->pesoActual ?? $ganadoSeleccionado->ultimoPeso, 2) }} kg
                    </span>
                </div>

                <!-- Ganancia Total de Peso -->
                <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase">Ganancia Total ($\Delta$ Peso)</span>
                    <div class="text-2xl font-black {{ $ganadoSeleccionado->ganancia_total >= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">
                        {{ $ganadoSeleccionado->ganancia_total >= 0 ? '+' : '' }}{{ number_format($ganadoSeleccionado->ganancia_total, 2) }} <span class="text-xs font-normal text-slate-500">kg</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Desde ingreso al rancho</span>
                </div>

                <!-- Ganancia Diaria de Peso (GDP) -->
                <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase">GDP Histórica (ADG)</span>
                    <div class="text-2xl font-black text-emerald-700 mt-1">
                        {{ number_format($ganadoSeleccionado->gdp_historica, 3) }} <span class="text-xs font-normal text-slate-500">kg/día</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">Promedio diario acumulado</span>
                </div>

                <!-- Meta / Peso Objetivo -->
                <div class="bg-white p-4 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-[11px] font-semibold text-slate-400 block uppercase">Peso Objetivo Venta</span>
                    <div class="text-2xl font-black text-amber-600 mt-1">
                        {{ number_format($ganadoSeleccionado->peso_objetivo_calculado, 1) }} <span class="text-xs font-normal text-slate-500">kg</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">
                        Faltan: {{ max(0, round($ganadoSeleccionado->peso_objetivo_calculado - $ganadoSeleccionado->ultimoPeso, 1)) }} kg
                    </span>
                </div>
            </div>

            <!-- 5. BITÁCORA HISTÓRICA DE PESAJES -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 space-y-4">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-weight-scale text-emerald-600"></i> Bitácora Completa de Pesajes
                    </h4>
                    <span class="text-xs text-slate-400">Total registros: {{ $ganadoSeleccionado->pesajes->count() }}</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left text-xs sm:text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-200">
                                <th class="p-3">Fecha Pesaje</th>
                                <th class="p-3">Peso en Báscula</th>
                                <th class="p-3">Peso Anterior</th>
                                <th class="p-3">$\Delta$ Ganancia (kg)</th>
                                <th class="p-3">Días</th>
                                <th class="p-3">GDP (kg/día)</th>
                                <th class="p-3">Báscula / Corral</th>
                                <th class="p-3">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($ganadoSeleccionado->pesajes as $pesaje)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-3 font-semibold text-slate-800">
                                        {{ $pesaje->fecha_pesaje->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3 font-black text-slate-900 font-mono">
                                        {{ number_format($pesaje->peso, 2) }} kg
                                    </td>
                                    <td class="p-3 text-slate-500 font-mono">
                                        {{ $pesaje->peso_anterior ? number_format($pesaje->peso_anterior, 2) . ' kg' : 'Inicial' }}
                                    </td>
                                    <td class="p-3 font-bold font-mono {{ ($pesaje->ganancia_peso ?? 0) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ ($pesaje->ganancia_peso ?? 0) >= 0 ? '+' : '' }}{{ number_format($pesaje->ganancia_peso ?? 0, 2) }} kg
                                    </td>
                                    <td class="p-3 text-slate-600">
                                        {{ $pesaje->dias_transcurridos ?? '-' }}
                                    </td>
                                    <td class="p-3 font-semibold font-mono text-emerald-700">
                                        {{ $pesaje->gdp !== null ? number_format($pesaje->gdp, 3) . ' kg/d' : '-' }}
                                    </td>
                                    <td class="p-3 text-slate-500 text-xs">
                                        {{ $pesaje->bascula ?: 'Báscula Principal' }} {{ $pesaje->lote_corral ? '(' . $pesaje->lote_corral . ')' : '' }}
                                    </td>
                                    <td class="p-3 text-slate-500 text-xs">
                                        {{ $pesaje->observaciones ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">
                                        <i class="fa-solid fa-weight-scale text-2xl mb-1 text-slate-300 block"></i>
                                        Aún no se han registrado pesajes subsecuentes para este animal.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: REGISTRAR NUEVO PESAJE               -->
            <!-- ========================================== -->
            <div x-show="modalPesajeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
                <div @click.outside="modalPesajeOpen = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-slate-100">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-weight-scale text-emerald-600"></i> Registrar Pesaje en Báscula
                        </h3>
                        <button @click="modalPesajeOpen = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('ganado.pesaje.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="ganado_id" value="{{ $ganadoSeleccionado->GanadoID }}">

                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs flex justify-between">
                            <div>
                                <span class="text-slate-400 uppercase font-bold block text-[10px]">Arete</span>
                                <strong class="text-slate-800 font-mono text-sm">{{ $ganadoSeleccionado->areteID }}</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-slate-400 uppercase font-bold block text-[10px]">Último Peso</span>
                                <strong class="text-slate-800 font-mono text-sm">{{ number_format($ganadoSeleccionado->ultimoPeso, 2) }} kg</strong>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nuevo Peso en Báscula (kg) *</label>
                            <input type="number" step="0.01" min="1" name="peso" required placeholder="Ej. 485.50"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition font-mono font-bold text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Fecha de Pesaje *</label>
                            <input type="date" name="fecha_pesaje" value="{{ date('Y-m-d') }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Báscula</label>
                                <input type="text" name="bascula" placeholder="Ej. Báscula Corral 1"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Corral / Lote</label>
                                <input type="text" name="lote_corral" value="{{ $ganadoSeleccionado->lote_potrero }}" placeholder="Ej. Corral Engorda"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Observaciones</label>
                            <textarea name="observaciones" rows="2" placeholder="Ej. Buen temperamento, condición corporal 4/5..."
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="modalPesajeOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-check"></i> Guardar y Calcular GDP
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- MODAL: REGISTRAR BAJA POR MORTALIDAD        -->
            <!-- ========================================== -->
            <div x-show="modalBajaOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
                <div @click.outside="modalBajaOpen = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-rose-100">
                    <div class="flex justify-between items-center border-b border-rose-100 pb-3">
                        <h3 class="font-bold text-rose-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-skull text-rose-600"></i> Registrar Baja por Muerte
                        </h3>
                        <button @click="modalBajaOpen = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
                        <strong class="font-bold block mb-1"><i class="fa-solid fa-triangle-exclamation"></i> Acción Irreversible:</strong>
                        El animal #{{ $ganadoSeleccionado->areteID }} se excluirá del inventario activo y no podrá ser vendido ni pesado nuevamente.
                    </div>

                    <form action="{{ route('ganado.bajas.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="ganado_id" value="{{ $ganadoSeleccionado->GanadoID }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Fecha de Fallecimiento *</label>
                            <input type="date" name="fecha_muerte" value="{{ date('Y-m-d') }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Causa de Muerte *</label>
                            <select name="causa_muerte" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm px-3 py-2.5 focus:ring-2 focus:ring-rose-500 focus:bg-white transition">
                                <option value="">Selecciona la causa médica u operativa...</option>
                                @foreach($causasMuerte as $causa)
                                    <option value="{{ $causa }}">{{ $causa }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Hallazgos de Necropsia</label>
                            <textarea name="necropsia" rows="2" placeholder="Lesiones pulmonares, timpanismo ruminal, etc..."
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs px-3 py-2 focus:ring-2 focus:ring-rose-500 focus:bg-white transition"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Observaciones / Disposición de restos</label>
                            <textarea name="observaciones" rows="2" placeholder="Fosa con cal viva, incineración, etc..."
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl text-xs px-3 py-2 focus:ring-2 focus:ring-rose-500 focus:bg-white transition"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="modalBajaOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white transition shadow-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation"></i> Confirmar y Dar de Baja
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @else
            <!-- Estado vacío cuando no hay arete buscado -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-12 text-center space-y-3">
                <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Listo para escanear o buscar arete</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Digita o escanea el número de arete arriba para consultar su bitácora de pesajes, estado de venta y expedientes.</p>
            </div>
        @endif

    </div>
</x-app-layout>
