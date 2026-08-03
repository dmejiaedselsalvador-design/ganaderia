<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Gestión de Proveedores') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden p-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Directorio de Proveedores</h3>
                        <p class="text-sm text-slate-500">Listado de ranchos y proveedores de ganado locales y regionales.</p>
                    </div>
                    <a href="#" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-user-plus"></i> Nuevo Proveedor
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                                <th class="p-3.5">Nombre / Rancho</th>
                                <th class="p-3.5">Contacto</th>
                                <th class="p-3.5">Teléfono</th>
                                <th class="p-3.5">Ciudad / Región</th>
                                <th class="p-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-3.5 font-bold text-slate-800">Ganadera del Norte S.A.</td>
                                <td class="p-3.5 text-slate-600">Ing. Taurino León</td>
                                <td class="p-3.5 text-slate-600">639-555-0192</td>
                                <td class="p-3.5 text-slate-600">Hermosillo, Sonora</td>
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
