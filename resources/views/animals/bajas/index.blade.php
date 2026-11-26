<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Encabezado -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-skull text-rose-600"></i> Bitácora de Bajas y Mortalidad
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Trazabilidad sanitaria, causas de defunción y porcentaje de pérdidas del ganado.
                    </p>
                </div>

                <a href="{{ route('compras.ganado.index') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Volver a Inventario
                </a>
            </div>

            <!-- TARJETAS DE MÉTRICAS DE MORTALIDAD -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs font-semibold uppercase text-slate-400 block">Total de Defunciones</span>
                    <div class="text-3xl font-black text-rose-600 mt-1">
                        {{ $metricas['totalMuertes'] }} <span class="text-xs font-normal text-slate-500">cabezas</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Registradas históricamente</span>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs font-semibold uppercase text-slate-400 block">Tasa de Mortalidad</span>
                    <div class="text-3xl font-black {{ $metricas['tasaMortalidad'] > 3.0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                        {{ $metricas['tasaMortalidad'] }}%
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Sobre el total de cabezas ingresadas</span>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs font-semibold uppercase text-slate-400 block">Causa Más Frecuente</span>
                    <div class="text-sm font-bold text-slate-800 mt-2 truncate">
                        {{ $metricas['muertesPorCausa']->first()->causa_muerte ?? 'Sin registros' }}
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">
                        {{ $metricas['muertesPorCausa']->first()->total ?? 0 }} casos confirmados
                    </span>
                </div>
            </div>

            <!-- TABLA DE REGISTROS DE MORTALIDAD -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-800">Historial Detallado de Bajas</h3>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200 tracking-wider">
                                <th class="p-3.5">Fecha</th>
                                <th class="p-3.5">Arete ID</th>
                                <th class="p-3.5">Categoría / Raza</th>
                                <th class="p-3.5">Causa de Muerte</th>
                                <th class="p-3.5">Último Peso</th>
                                <th class="p-3.5">Necropsia / Hallazgos</th>
                                <th class="p-3.5">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bajas as $baja)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-3.5 font-semibold text-slate-800">
                                        {{ $baja->fecha_muerte->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3.5 font-bold font-mono text-slate-900">
                                        <a href="{{ route('compras.ganado.perfil', ['arete' => $baja->ganado->areteID ?? '']) }}"
                                           class="hover:text-emerald-600 transition flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            {{ $baja->ganado->areteID ?? 'N/D' }}
                                        </a>
                                    </td>
                                    <td class="p-3.5 text-slate-700">
                                        {{ $baja->ganado->categoria ?? 'N/D' }} ({{ $baja->ganado->raza ?? 'General' }})
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                            {{ $baja->causa_muerte }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 font-mono text-slate-800">
                                        {{ $baja->ultimo_peso ? number_format($baja->ultimo_peso, 2) . ' kg' : '-' }}
                                    </td>
                                    <td class="p-3.5 text-slate-500 text-xs max-w-xs truncate" title="{{ $baja->necropsia }}">
                                        {{ $baja->necropsia ?: ($baja->observaciones ?: 'Sin observaciones') }}
                                    </td>
                                    <td class="p-3.5 text-slate-600 text-xs">
                                        {{ $baja->usuario->name ?? 'Sistema' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        <i class="fa-solid fa-heart-pulse text-3xl mb-2 text-slate-300 block"></i>
                                        No se han registrado bajas por mortalidad en el rancho.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-2">
                    {{ $bajas->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
