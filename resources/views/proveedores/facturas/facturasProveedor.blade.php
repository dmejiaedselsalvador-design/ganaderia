<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden p-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Facturas de Proveedores</h3>
                        <p class="text-sm text-slate-500">Lista de Facturas y Balance de Cuentas</p>
                    </div>
                    <a href="{{ route('proveedores.facturas.crear') }}"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow transition">
                        <i class="fa-solid fa-plus"></i> Crear Nueva Factura
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                                <th class="p-3.5">Proveedor / Contacto</th>
                                <th class="p-3.5"># Factura</th>
                                <th class="p-3.5">Cantidad Ganado</th>
                                <th class="p-3.5">Monto a pagar</th>
                                <th class="p-3.5">Estatus</th>
                                <th class="p-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($proveedores as $proveedor)
                                <tr class="hover:bg-slate-50/50 transition">

                                    <!-- 1. Proveedor y botón de acción rápido -->
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800">
                                            {{ $proveedor->proveedorData->nombreContacto }}</div>
                                        <div class="text-xs text-slate-500 mb-2">
                                            {{ $proveedor->proveedorData->telefono ?? 'Sin contacto' }}</div>

                                       <a href="{{ route('compras.nuevo.ganado', ['factura_id' => $proveedor->id]) }}"
    class="inline-flex items-center px-3 py-1 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
    + Agregar Ganado
</a>
                                    </td>

                                    <!-- 2. actuas -->
                                    <td class="p-3.5 font-medium text-slate-700">
                                        {{ $proveedor->numeroFactura ?? 'N/A' }}
                                    </td>

                                    <td class="p-3.5 font-medium text-slate-700">
                                        {{ $proveedor->cantidad_ganado ?? 'N/A' }}
                                    </td>

                                    <!-- 3. Total Facturado de Ganado (fac.total_facturas) -->
                                    <td class="p-3.5 font-medium text-slate-700">
                                        {{ formatoPesos($proveedor->montoTotal ?? 0, 2) }}
                                    </td>

                                    <!-- 4. Saldo / Liquidación Neta -->
                                    <td class="p-3.5">
                                        @php
                                            $saldo = $proveedor->saldo_liquidacion ?? 0;
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-md
                                            {{ $saldo < 0 ? 'bg-red-100 text-red-700' : ($saldo > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ formatoPesos($saldo, 2) }}
                                        </span>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            @if ($saldo < 0)
                                                (Le debes)
                                            @elseif($saldo > 0)
                                                (Saldo a favor)
                                            @else
                                                (Saldado)
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Estatus -->
                                    <td class="p-3.5">
                                        <span
                                            class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                            {{ ucfirst($proveedor->estado) }}
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="p-3.5 text-center space-x-2">
                                        <a href="{{ route('proveedor.editar', $proveedor->id) }}"
                                            class="text-slate-500 hover:text-emerald-600 transition">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
