<x-app-layout>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden p-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Catálogo de Cabezas de Ganado</h3>
                        <p class="text-sm text-slate-500">Control de aretes SINIIGA, pesos en báscula y estatus actual.</p>
                    </div>
                    <a href="{{ route('compras.nuevo.ganado') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-plus"></i> Registrar Animal
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                                <th class="p-3.5">Arete / ID</th>
                                <th class="p-3.5">Proveedor</th>
                                <th class="p-3.5">Raza / Sexo</th>
                                <th class="p-3.5">Peso Actual</th>
                                <th class="p-3.5">Costo / Precio</th>
                                <th class="p-3.5">Estatus</th>
                                <th class="p-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-3.5 font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    MEX-849201
                                </td>
                                <td class="p-3.5 text-slate-600">Rancho El Sausalito</td>
                                <td class="p-3.5 text-slate-600">Angus <span class="text-xs text-slate-400">/ Macho</span></td>
                                <td class="p-3.5 font-medium text-emerald-700">480.00 kg</td>
                                <td class="p-3.5 font-semibold text-slate-700">{{ formatoPesos(24500) }}</td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        Activo
                                    </span>
                                </td>
                                <td class="p-3.5 text-center space-x-2">
                                    <button class="text-slate-500 hover:text-emerald-600 transition"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <button class="text-slate-500 hover:text-red-600 transition"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
