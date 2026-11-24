<x-app-layout>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mt-6">
    <!-- Encabezado de la Sección -->
    <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Control de Adelantos a Proveedores</h3>
            <p class="text-xs text-slate-500 mt-0.5">Listado de adelantos entregados pendientes de liquidación.</p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
            Pendientes de cobro
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <th class="py-3.5 px-6">Proveedor / Contacto</th>
                    <th class="py-3.5 px-6">Concepto</th>
                    <th class="py-3.5 px-6">Monto (Adelanto)</th>
                    <th class="py-3.5 px-6">Fecha</th>
                    <th class="py-3.5 px-6 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-600" id="tabla-deudores-body">
                <!-- Los datos se inyectarán aquí con JavaScript o directamente desde Blade -->
                @foreach($deudores as $deudor)
                    <tr>
                        <td class="py-3.5 px-6">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-800">{{ $deudor->proveedor->nombreProoveedor }}</span>
                                <span class="text-xs text-slate-400">({{ $deudor->proveedor->nombreContacto }})</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-6">{{ $deudor->concepto }}</td>
                        <td class="py-3.5 px-6">${{ number_format($deudor->dinero, 2) }}</td>
                        <td class="py-3.5 px-6">{{ \Carbon\Carbon::parse($deudor->date)->format('d/m/Y') }}</td>
                        <td class="py-3.5 px-6 text-center">
                            @if($deudor->status === 'entregado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    Pendiente
                                </span>
                            @elseif($deudor->status === 'liquidado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                 Liquidado
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-app-layout>
