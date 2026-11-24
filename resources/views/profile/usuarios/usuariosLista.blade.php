<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Encabezado de sección -->
            <div class="flex justify-between items-center bg-white p-6 shadow-sm sm:rounded-2xl border border-slate-100">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Gestión de Personal</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Listado de usuarios registrados en el sistema ganadero</p>
                </div>
                <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm flex items-center gap-2">
                    <span>➕</span> Nuevo Usuario
                </a>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-slate-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-600 text-xs font-bold uppercase tracking-wider">
                                <th class="py-4 px-6">Nombre</th>
                                <th class="py-4 px-6">Email</th>
                                <th class="py-4 px-6">Rol</th>
                                <th class="py-4 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse ($users as $user)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-6 font-semibold text-slate-900">{{ $user->name }}</td>
                                    <td class="py-4 px-6 text-slate-600">{{ $user->email }}</td>
                                    <td class="py-4 px-6">
                                        @foreach ($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <a href="#" class="text-slate-500 hover:text-emerald-600 font-medium transition">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 text-sm">
                                        No hay usuarios registrados en este momento.
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
