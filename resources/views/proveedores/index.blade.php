<x-app-layout>


    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden p-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Catálogo de Vendedores de Ganado</h3>
                        <p class="text-sm text-slate-500">Lista de Vendedores</p>
                    </div>
                    <a href="{{ route('proveedor.create') }}"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow transition">
                        <i class="fa-solid fa-plus"></i> Crear Nuevo Proveedor
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                                <th class="p-3.5">Adelanto</th>
                                <th class="p-3.5">Contacto</th>
                                <th class="p-3.5">Costo Adelanto</th>


                                <th class="p-3.5">Estatus</th>
                                <th class="p-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach ($proveedores as $proveedor)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-3.5 font-semibold text-slate-700">
                                        <a href="{{ route('adelantos.proveedores.index') }}"
                                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Crear Adelanto
                                        </a>
                                    </td>
                                      <td class="p-3.5 text-slate-600">{{ $proveedor->nombreContacto }}</td>
                                      <td>
                                   {{ formatoPesos($proveedor->total_adelanto ?? 0, 2) }}

                                      </td>


                            <td class="p-3.5">
                                <span
                                    class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    Activo
                                </span>
                            </td>

                            <td class="p-3.5 text-center space-x-2">
                                <button class="text-slate-500 hover:text-emerald-600 transition"><i
                                        class="fa-solid fa-pen-to-square"></i></button>
                                <button class="text-slate-500 hover:text-red-600 transition"><i
                                        class="fa-solid fa-trash"></i></button>
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
