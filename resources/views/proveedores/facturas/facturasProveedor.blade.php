<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Tarjeta Principal Estilizada -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">

                <!-- Encabezado de la Sección -->
                <div class="p-6 sm:p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-slate-50/50 to-white">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Facturas de Proveedores</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Gestión de listas de facturas, balances de cuentas y liquidaciones</p>
                    </div>
                    <a href="{{ route('proveedores.facturas.crear') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-semibold text-sm rounded-xl hover:bg-indigo-700 shadow-sm hover:shadow transition-all duration-200">
                        <i class="fa-solid fa-plus text-xs"></i> Crear Nueva Factura
                    </a>
                </div>

                <!-- Tabla de Datos Mejorada -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-xs uppercase tracking-wider font-semibold">
                                <th class="py-3.5 px-6">Proveedor / Contacto</th>
                                <th class="py-3.5 px-6"># Factura</th>
                                <th class="py-3.5 px-6">Cantidad Ganado</th>
                                <th class="py-3.5 px-6">Monto a Pagar</th>
                                <th class="py-3.5 px-6">Saldo / Balance</th>
                                <th class="py-3.5 px-6">Estatus</th>
                                <th class="py-3.5 px-6 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse ($proveedores as $proveedor)
                                <tr class="hover:bg-slate-50/80 transition-colors">

                                    <!-- 1. Proveedor y botón de acción rápido -->
                                    <td class="py-4 px-6 align-middle">
                                        <div class="font-bold text-slate-800 text-base">
                                            {{ $proveedor->proveedorData->nombreContacto ?? 'N/D' }}
                                        </div>
                                        <div class="text-xs font-medium text-slate-400 mb-2.5">
                                            <i class="fa-solid fa-phone mr-1"></i> {{ $proveedor->proveedorData->telefono ?? 'Sin contacto' }}
                                        </div>

                                        <a href="{{ route('compras.nuevo.ganado', ['factura_id' => $proveedor->id]) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg font-semibold text-xs uppercase tracking-wide hover:bg-emerald-600 hover:text-white transition-all duration-150 shadow-2xs">
                                            <i class="fa-solid fa-plus text-[10px]"></i> Agregar Ganado
                                        </a>
                                    </td>

                                    <!-- 2. # Factura -->
                                    <td class="py-4 px-6 align-middle font-semibold text-slate-700">
                                        {{ $proveedor->numeroFactura ?? 'S/N' }}
                                    </td>

                                    <!-- 3. Cantidad Ganado -->
                                    <td class="py-4 px-6 align-middle font-medium text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md text-xs font-bold">
                                            {{ $proveedor->cantidad_ganado ?? '0' }} cabezas
                                        </span>
                                    </td>

                                    <!-- 4. Total Facturado de Ganado -->
                                    <td class="py-4 px-6 align-middle font-bold text-slate-800">
                                        {{ formatoPesos($proveedor->montoTotal ?? 0, 2) }}
                                    </td>

                                    <!-- 5. Saldo / Liquidación Neta -->
                                    <td class="py-4 px-6 align-middle">
                                        @php
                                            $saldo = $proveedor->saldo_liquidacion ?? 0;
                                        @endphp
                                        <div class="inline-flex flex-col">
                                            <span class="inline-flex items-center px-3 py-1 text-xs font-black rounded-lg w-max
                                                {{ $saldo < 0 ? 'bg-red-50 text-red-700 border border-red-200' : ($saldo > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700') }}">
                                                {{ formatoPesos($saldo, 2) }}
                                            </span>
                                            <span class="text-[10px] font-bold tracking-wide uppercase mt-1
                                                @if($saldo < 0) text-red-500 @elseif($saldo > 0) text-emerald-600 @else text-slate-400 @endif">
                                                @if ($saldo < 0)
                                                    ● Saldo Pendiente
                                                @elseif($saldo > 0)
                                                    ● A Favor
                                                @else
                                                    ● Saldado
                                                @endif
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Estatus -->
                                    <td class="py-4 px-6 align-middle">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100/80 text-emerald-800 uppercase tracking-wider">
                                            {{ ucfirst($proveedor->estado ?? 'activo') }}
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-4 px-6 align-middle text-center">
                                        <a href="{{ route('proveedores.facturas.liquidar', $proveedor->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-200 shadow-sm">
                                            <i class="fa-solid fa-money-bill-wave text-xs"></i> Liquidar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 italic">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <i class="fa-solid fa-folder-open text-3xl text-slate-300"></i>
                                            <p class="text-sm font-medium">No hay facturas registradas en este momento.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
