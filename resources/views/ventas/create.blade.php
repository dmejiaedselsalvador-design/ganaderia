<x-app-layout>
    <div class="py-6" x-data="crearVentaApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-cart-shopping text-emerald-600"></i> Registro de Venta de Ganado
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Selección individual o por lote de cabezas activas, pesaje de salida y liquidación comercial.
                    </p>
                </div>

                <a href="{{ route('ventas.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Volver a Ventas
                </a>
            </div>

            @if($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-sm shadow-xs">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Errores de validación
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('ventas.store') }}" method="POST" id="formVentaGanado" class="space-y-6">
                @csrf

                <!-- SECCIÓN 1: DATOS DEL COMPRADOR Y CONDICIONES -->
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-5">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i class="fa-solid fa-user-check text-emerald-600"></i> Datos del Comprador y Condiciones de Venta
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Cliente / Comprador *</label>
                            <input type="text" name="cliente_nombre" required placeholder="Ej. Carnes del Norte S.A. o Don Alberto Gómez"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Teléfono de Contacto</label>
                            <input type="text" name="cliente_telefono" placeholder="Ej. 662-123-4567"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">RFC / Identificación</label>
                            <input type="text" name="cliente_identificacion" placeholder="Ej. RFC o INE"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Fecha de Venta *</label>
                            <input type="date" name="fecha_venta" value="{{ date('Y-m-d') }}" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Destino del Ganado</label>
                            <input type="text" name="destino" placeholder="Ej. Rastro TIF, Engorda Sonora, Exportación"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Modalidad de Cobro *</label>
                            <select name="modalidad_pago" x-model="modalidad" @change="recalcularTotales()"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition font-semibold">
                                <option value="por_kilo">Venta por Kilo ($/kg en báscula)</option>
                                <option value="precio_fijo">Precio Fijo por Cabeza ($/cabeza)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">
                                Precio Base Global <span x-text="modalidad === 'por_kilo' ? '($/kg MXN)' : '($/cabeza MXN)'"></span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="number" step="0.01" min="0" x-model.number="precioGlobal" placeholder="0.00"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3.5 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition font-mono font-bold">
                                <button type="button" @click="aplicarPrecioGlobal()"
                                        title="Aplicar este precio a todos los animales seleccionados"
                                        class="px-3 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition shrink-0">
                                    Aplicar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: SELECCIÓN DE ANIMALES ACTIVOS -->
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-emerald-600"></i> Seleccionar Ganado a Vender
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Marca las cabezas que saldrán en este lote. Puedes ajustar el peso de báscula de salida y precio para cada animal.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="seleccionarListosParaVenta()"
                                    class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-bullseye"></i> Seleccionar sólo "Listos para Venta"
                            </button>
                            <button type="button" @click="toggleTodos()"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                                <span x-text="todosMarcados ? 'Desmarcar Todos' : 'Marcar Todos'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200 tracking-wider">
                                    <th class="p-3.5 w-12 text-center">Sel.</th>
                                    <th class="p-3.5">Arete ID</th>
                                    <th class="p-3.5">Categoría / Raza</th>
                                    <th class="p-3.5">Peso Salida (kg)</th>
                                    <th class="p-3.5">
                                        <span x-text="modalidad === 'por_kilo' ? 'Precio $/kg (MXN)' : 'Precio Cabeza (MXN)'"></span>
                                    </th>
                                    <th class="p-3.5 text-right">Subtotal Estimado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($animalesDisponibles as $index => $animal)
                                    <tr class="hover:bg-slate-50/70 transition"
                                        :class="seleccionados[{{ $animal->GanadoID }}] ? 'bg-emerald-50/40' : ''">
                                        <!-- Checkbox -->
                                        <td class="p-3.5 text-center">
                                            <input type="checkbox"
                                                   value="{{ $animal->GanadoID }}"
                                                   x-model="seleccionados[{{ $animal->GanadoID }}]"
                                                   @change="recalcularTotales()"
                                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                                        </td>

                                        <!-- Arete -->
                                        <td class="p-3.5 font-bold font-mono text-slate-800">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ $animal->areteID }}</span>
                                                @if($animal->esta_listo_venta)
                                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300" title="Alcanzó peso de venta">
                                                        🎯 LISTO
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Categoría / Raza -->
                                        <td class="p-3.5 text-slate-700">
                                            <div class="font-semibold">{{ $animal->categoria }} ({{ $animal->sexo }})</div>
                                            <div class="text-[11px] text-slate-400">{{ $animal->raza ?: 'Cruzado' }}</div>
                                        </td>

                                        <!-- Peso de salida -->
                                        <td class="p-3.5">
                                            <input type="number" step="0.01" min="1"
                                                   :disabled="!seleccionados[{{ $animal->GanadoID }}]"
                                                   x-model.number="animales[{{ $animal->GanadoID }}].peso_venta"
                                                   @input="recalcularTotales()"
                                                   class="w-28 bg-white border border-slate-200 rounded-lg text-xs px-2.5 py-1.5 focus:ring-2 focus:ring-emerald-500 font-mono font-bold text-slate-800 disabled:bg-slate-100 disabled:text-slate-400">
                                            <span class="text-xs text-slate-400 ml-1">kg</span>
                                        </td>

                                        <!-- Precio Unitario -->
                                        <td class="p-3.5">
                                            <div class="relative w-32">
                                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-slate-400">$</span>
                                                <input type="number" step="0.01" min="0"
                                                       :disabled="!seleccionados[{{ $animal->GanadoID }}]"
                                                       x-model.number="animales[{{ $animal->GanadoID }}].precio_unitario"
                                                       @input="recalcularTotales()"
                                                       placeholder="0.00"
                                                       class="w-full pl-6 pr-2 bg-white border border-slate-200 rounded-lg text-xs py-1.5 focus:ring-2 focus:ring-emerald-500 font-mono font-bold text-slate-800 disabled:bg-slate-100 disabled:text-slate-400">
                                            </div>
                                        </td>

                                        <!-- Subtotal por fila -->
                                        <td class="p-3.5 text-right font-mono font-bold text-slate-800">
                                            <span x-text="formatoMoneda(calcularSubtotalFila({{ $animal->GanadoID }}))">
                                                $ 0.00 MXN
                                            </span>
                                        </td>

                                        <!-- Inputs ocultos para enviar en el POST -->
                                        <template x-if="seleccionados[{{ $animal->GanadoID }}]">
                                            <div>
                                                <input type="hidden" :name="'animales[' + {{ $animal->GanadoID }} + '][ganado_id]'" value="{{ $animal->GanadoID }}">
                                                <input type="hidden" :name="'animales[' + {{ $animal->GanadoID }} + '][peso_venta]'" :value="animales[{{ $animal->GanadoID }}].peso_venta">
                                                <input type="hidden" :name="'animales[' + {{ $animal->GanadoID }} + '][precio_unitario]'" :value="animales[{{ $animal->GanadoID }}].precio_unitario">
                                            </div>
                                        </template>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-400">
                                            No hay ganado activo disponible para vender en este momento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECCIÓN 3: RESUMEN FINANCIERO DE LIQUIDACIÓN EN MXN -->
                <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-lg border border-slate-800 space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-800 pb-4">
                        <div>
                            <span class="text-xs uppercase font-mono tracking-widest text-emerald-400 block font-bold">Liquidación Comercial de Venta</span>
                            <h4 class="text-xl font-black mt-0.5">Resumen Financiero (Moneda Nacional Mexicana)</h4>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">Total Cabezas a Facturar</span>
                            <span class="text-2xl font-black font-mono text-emerald-400" x-text="totalCabezas">0</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 font-mono">
                        <div>
                            <span class="text-xs text-slate-400 uppercase block">Peso Total en Báscula</span>
                            <div class="text-xl font-bold text-slate-100 mt-1">
                                <span x-text="pesoTotalKg.toFixed(2)">0.00</span> kg
                            </div>
                        </div>

                        <div>
                            <span class="text-xs text-slate-400 uppercase block">Subtotal Calculado</span>
                            <div class="text-xl font-bold text-slate-100 mt-1" x-text="formatoMoneda(subtotalCalculado)">
                                $ 0.00 MXN
                            </div>
                        </div>

                        <div>
                            <label class="text-xs text-slate-400 uppercase block mb-1">Descuento / Ajuste ($ MXN)</label>
                            <input type="number" step="0.01" min="0" name="descuento" x-model.number="descuento" @input="recalcularTotales()"
                                   placeholder="0.00"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-xl text-sm px-3 py-1.5 text-white font-mono focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        </div>

                        <div class="p-3 bg-emerald-950/60 rounded-xl border border-emerald-500/40 text-right">
                            <span class="text-xs uppercase tracking-wider text-emerald-400 block font-bold">Monto Total a Cobrar</span>
                            <div class="text-2xl sm:text-3xl font-black text-emerald-300 font-mono mt-1" x-text="formatoMoneda(totalFinal)">
                                $ 0.00 MXN
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Notas u Observaciones del Contrato / Guía de Tránsito</label>
                        <textarea name="notas" rows="2" placeholder="Ej. Pago contra entrega en rastro. Transporte a cargo del comprador..."
                                  class="w-full bg-slate-800 border border-slate-700 rounded-xl text-xs px-3.5 py-2 text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:outline-none"></textarea>
                    </div>

                    <!-- BOTÓN DE CONFIRMACIÓN -->
                    <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-3 border-t border-slate-800">
                        <a href="{{ route('ventas.index') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 text-center transition">
                            Cancelar
                        </a>

                        <button type="submit"
                                :disabled="totalCabezas === 0"
                                class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs sm:text-sm font-bold uppercase tracking-wider bg-emerald-500 hover:bg-emerald-400 text-slate-950 transition shadow-lg flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-check-double text-base"></i>
                            <span>Confirmar Venta y Liquidar (<span x-text="totalCabezas"></span> Cabezas)</span>
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

    <!-- Script de reactividad de Alpine.js para la venta -->
    <script>
        function crearVentaApp() {
            // Inicializar catálogo desde Blade
            const animalesRaw = @json($animalesDisponibles);
            const preseleccionados = @json($seleccionadosIds);

            const animalesObj = {};
            const seleccionadosObj = {};

            animalesRaw.forEach(a => {
                animalesObj[a.GanadoID] = {
                    id: a.GanadoID,
                    arete: a.areteID,
                    peso_venta: parseFloat(a.ultimoPeso) || 0,
                    precio_unitario: 50.00, // Precio base por kg sugerido
                    esta_listo: Boolean(a.esta_listo_venta)
                };

                // Si viene preseleccionado por URL
                if (preseleccionados.includes(String(a.GanadoID)) || preseleccionados.includes(Number(a.GanadoID))) {
                    seleccionadosObj[a.GanadoID] = true;
                } else {
                    seleccionadosObj[a.GanadoID] = false;
                }
            });

            return {
                modalidad: 'por_kilo',
                precioGlobal: 50.00,
                descuento: 0.00,
                animales: animalesObj,
                seleccionados: seleccionadosObj,
                todosMarcados: false,

                totalCabezas: 0,
                pesoTotalKg: 0,
                subtotalCalculado: 0,
                totalFinal: 0,

                init() {
                    this.recalcularTotales();
                },

                calcularSubtotalFila(id) {
                    if (!this.seleccionados[id]) return 0;
                    const item = this.animales[id];
                    if (!item) return 0;

                    if (this.modalidad === 'por_kilo') {
                        return (item.peso_venta || 0) * (item.precio_unitario || 0);
                    } else {
                        return (item.precio_unitario || 0);
                    }
                },

                recalcularTotales() {
                    let cabezas = 0;
                    let peso = 0;
                    let subtotal = 0;

                    Object.keys(this.seleccionados).forEach(id => {
                        if (this.seleccionados[id]) {
                            cabezas++;
                            const item = this.animales[id];
                            peso += (item.peso_venta || 0);
                            subtotal += this.calcularSubtotalFila(id);
                        }
                    });

                    this.totalCabezas = cabezas;
                    this.pesoTotalKg = peso;
                    this.subtotalCalculado = subtotal;
                    this.totalFinal = Math.max(0, subtotal - (parseFloat(this.descuento) || 0));
                },

                aplicarPrecioGlobal() {
                    if (this.precioGlobal < 0) return;
                    Object.keys(this.animales).forEach(id => {
                        if (this.seleccionados[id]) {
                            this.animales[id].precio_unitario = this.precioGlobal;
                        }
                    });
                    this.recalcularTotales();
                },

                seleccionarListosParaVenta() {
                    Object.keys(this.animales).forEach(id => {
                        this.seleccionados[id] = Boolean(this.animales[id].esta_listo);
                    });
                    this.recalcularTotales();
                },

                toggleTodos() {
                    this.todosMarcados = !this.todosMarcados;
                    Object.keys(this.seleccionados).forEach(id => {
                        this.seleccionados[id] = this.todosMarcados;
                    });
                    this.recalcularTotales();
                },

                formatoMoneda(val) {
                    const num = parseFloat(val) || 0;
                    return '$ ' + num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' MXN';
                }
            };
        }
    </script>
</x-app-layout>
