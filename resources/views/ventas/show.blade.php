<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensaje de éxito -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-center gap-3 text-emerald-800 text-sm shadow-xs print:hidden">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Botones de Acción (Ocultos al Imprimir) -->
            <div class="flex items-center justify-between print:hidden">
                <a href="{{ route('ventas.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Listado
                </a>

                <div class="flex items-center gap-2">
                    <button onclick="window.print()"
                            class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                        <i class="fa-solid fa-print"></i> Imprimir Liquidación
                    </button>
                    <a href="{{ route('ventas.create') }}"
                       class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                        <i class="fa-solid fa-plus"></i> Nueva Venta
                    </a>
                </div>
            </div>

            <!-- COMPROBANTE EJECUTIVO IMPRIMIBLE -->
            <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-8 sm:p-12 space-y-8 print:shadow-none print:border-none print:p-0">

                <!-- ENCABEZADO EJECUTIVO -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b-2 border-slate-900 pb-6 gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-bold text-lg">
                                <i class="fa-solid fa-cow"></i>
                            </div>
                            <span class="font-['Playfair_Display'] text-2xl font-black tracking-tight text-slate-900">
                                GANADERÍA <span class="text-amber-600">TAURINO</span>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 uppercase tracking-widest font-mono">
                            Comprobante Oficial de Salida y Liquidación de Ganado
                        </p>
                    </div>

                    <div class="text-left sm:text-right bg-slate-50 p-4 rounded-2xl border border-slate-200 font-mono">
                        <span class="text-xs font-bold text-slate-400 block uppercase">Folio de Venta</span>
                        <span class="text-xl font-black text-emerald-800">{{ $venta->folio_venta }}</span>
                        <span class="text-[11px] text-slate-500 block mt-1">Fecha: {{ $venta->fecha_venta->format('d/m/Y') }}</span>
                    </div>
                </div>

                <!-- TARJETA DEL CLIENTE Y DETALLES DE DESPACHO -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-slate-50 p-6 rounded-2xl border border-slate-100 text-xs">
                    <div>
                        <span class="font-bold text-slate-400 uppercase text-[10px] block tracking-wider">Comprador / Razón Social</span>
                        <strong class="text-slate-900 text-sm block mt-0.5">{{ $venta->cliente_nombre }}</strong>
                        <span class="text-slate-500 mt-1 block">Tel: {{ $venta->cliente_telefono ?: 'No registrado' }}</span>
                        @if($venta->cliente_identificacion)
                            <span class="text-slate-500 block font-mono">RFC/ID: {{ $venta->cliente_identificacion }}</span>
                        @endif
                    </div>

                    <div>
                        <span class="font-bold text-slate-400 uppercase text-[10px] block tracking-wider">Destino de la Guía</span>
                        <strong class="text-slate-900 text-sm block mt-0.5">{{ $venta->destino ?: 'Corral / Entrega Local' }}</strong>
                        <span class="text-slate-500 mt-1 block">Modalidad: <strong class="text-slate-700 uppercase">{{ $venta->modalidad_pago === 'por_kilo' ? 'Por Kilo en Báscula' : 'Precio Fijo' }}</strong></span>
                    </div>

                    <div>
                        <span class="font-bold text-slate-400 uppercase text-[10px] block tracking-wider">Operador Responsable</span>
                        <strong class="text-slate-900 text-sm block mt-0.5">{{ $venta->usuario->name ?? 'Administración' }}</strong>
                        <span class="text-emerald-700 font-semibold mt-1 block flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-xs"></i> Estatus: {{ ucfirst($venta->estado) }}
                        </span>
                    </div>
                </div>

                <!-- TABLA DE ANIMALES LIQUIDADOS -->
                <div class="space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm uppercase tracking-wide border-b border-slate-200 pb-2">
                        Desglose de Cabezas Despachadas ({{ $venta->total_cabezas }} animales)
                    </h4>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="bg-slate-100 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200 tracking-wider">
                                    <th class="p-3">#</th>
                                    <th class="p-3">Arete ID</th>
                                    <th class="p-3">Categoría / Raza</th>
                                    <th class="p-3 text-right">Peso Salida</th>
                                    <th class="p-3 text-right">Precio Unitario</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono">
                                @foreach($venta->detalles as $i => $detalle)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 text-slate-400 font-sans text-xs">{{ $i + 1 }}</td>
                                        <td class="p-3 font-bold text-slate-900">{{ $detalle->arete_snapshot }}</td>
                                        <td class="p-3 text-slate-700 font-sans text-xs">
                                            {{ $detalle->categoria_snapshot }} ({{ $detalle->raza_snapshot ?: 'General' }})
                                        </td>
                                        <td class="p-3 text-right font-bold text-slate-800">
                                            {{ number_format($detalle->peso_venta, 2) }} kg
                                        </td>
                                        <td class="p-3 text-right text-slate-600">
                                            {{ formatoPesos($detalle->precio_unitario) }}
                                        </td>
                                        <td class="p-3 text-right font-black text-slate-900">
                                            {{ formatoPesos($detalle->subtotal) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-mono font-bold text-xs sm:text-sm">
                                <tr>
                                    <td colspan="3" class="p-3 text-right uppercase font-sans text-slate-600">Totales Acumulados:</td>
                                    <td class="p-3 text-right text-slate-900 font-black">{{ number_format($venta->peso_total_kg, 2) }} kg</td>
                                    <td class="p-3 text-right text-slate-500 font-normal">Subtotal:</td>
                                    <td class="p-3 text-right text-slate-900 font-black">{{ formatoPesos($venta->subtotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- RESUMEN FINAL MONETARIO EN MONEDA MEXICANA -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 pt-4 border-t-2 border-slate-900">
                    <div class="text-xs text-slate-500 max-w-sm">
                        <strong class="text-slate-700 block mb-1">Notas de la Operación:</strong>
                        {{ $venta->notas ?: 'Operación comercial concretada conforme a pesaje oficial de báscula de rancho. Los animales pasan a custodia y responsabilidad del comprador.' }}
                    </div>

                    <div class="w-full sm:w-80 space-y-2 font-mono text-xs text-right">
                        <div class="flex justify-between text-slate-500">
                            <span>Subtotal Ganado:</span>
                            <span class="font-bold text-slate-800">{{ formatoPesos($venta->subtotal) }}</span>
                        </div>
                        @if($venta->descuento > 0)
                            <div class="flex justify-between text-rose-600">
                                <span>Descuento / Ajuste:</span>
                                <span class="font-bold">- {{ formatoPesos($venta->descuento) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base sm:text-lg font-black text-emerald-800 pt-2 border-t border-slate-200">
                            <span class="font-sans uppercase">Total a Liquidar:</span>
                            <span>{{ formatoPesos($venta->total_venta) }}</span>
                        </div>
                    </div>
                </div>

                <!-- FIRMAS -->
                <div class="grid grid-cols-2 gap-12 pt-16 text-center text-xs text-slate-500 print:pt-20">
                    <div class="border-t border-slate-300 pt-2">
                        <strong class="text-slate-800 block text-sm">{{ $venta->cliente_nombre }}</strong>
                        <span>Firma del Comprador / Receptor</span>
                    </div>

                    <div class="border-t border-slate-300 pt-2">
                        <strong class="text-slate-800 block text-sm">{{ $venta->usuario->name ?? 'Ganadería Taurino' }}</strong>
                        <span>Por Ganadería Taurino / Autorización</span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
