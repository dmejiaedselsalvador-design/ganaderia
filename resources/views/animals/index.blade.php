<x-app-layout>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden p-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Catálogo de Cabezas de Ganado</h3>
                        <p class="text-sm text-slate-500">Control de aretes SINIIGA, pesos en báscula y estatus actual.
                        </p>
                    </div>
                    <a href="{{ route('compras.nuevo.ganado') }}"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-plus"></i> Registrar Animal
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                                <th class="p-3.5">Arete / ID</th>
                                <th class="p-3.5">Proveedor</th>
                                <th class="p-3.5">Raza / Sexo</th>
                                <th class="p-3.5">Peso Actual</th>
                                <th class="p-3.5">Costo / Precio</th>
                                <th class="p-3.5">Estatus</th>

                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                              @foreach ($ganados as $ganado)
                            <tr class="hover:bg-slate-50/50 transition">


                                    <td class="p-3.5 font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                   {{ $ganado->areteID }}
                                    </td>


                                <td class="p-3.5 text-slate-600">{{ $ganado->factura->proveedor->nombreContacto ?? 'Sin proveedor' }}</td>
                               <td class="p-3.5 text-slate-600">
            {{ $ganado->raza ?? 'Sin especificar' }}
            <span class="text-xs text-slate-400">/ {{ $ganado->genero }}</span>
        </td>
                            <td class="p-3.5 font-medium text-emerald-700">
            {{ number_format($ganado->ultimoPeso, 2) }} kg
        </td>
                                <td class="p-3.5 font-semibold text-slate-700">
            {{ formatoPesos($ganado->precioCompra ?? 0) }}
        </td>
                               <td class="p-3.5">
            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                @if($ganado->status == 'Activo') bg-emerald-100 text-emerald-800
                @elseif($ganado->status == 'Vendido') bg-blue-100 text-blue-800
                @else bg-red-100 text-red-800 @endif">
                {{ $ganado->status }}
            </span>
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
