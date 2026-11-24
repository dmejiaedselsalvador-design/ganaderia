<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-center gap-3 text-emerald-800 text-sm shadow-xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- ENCABEZADO Y BOTÓN NUEVA VENTA -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i> Módulo de Ventas de Ganado
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Liquidaciones comerciales de ganado en pie, ventas por lote y arqueo de ingresos.
                    </p>
                </div>

                <a href="{{ route('ventas.create') }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 shadow-xs">
                    <i class="fa-solid fa-plus"></i> Nueva Venta de Ganado
                </a>
            </div>

            <!-- TARJETAS DE INGRESOS Y RESUMEN FINANCIERO EN MONEDA MEXICANA -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Total Ingresos MXN -->
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs font-semibold uppercase text-slate-400 block">Total Ingresos por Ventas</span>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-700 font-mono mt-1">
                        {{ formatoPesos($totalIngresosMXN) }}
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Facturado acumulado en moneda nacional</span>
                </div>

                <!-- Cabezas Vendidas -->
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs font-semibold uppercase text-slate-400 block">Cabezas de Ganado Vendidas</span>
                    <div class="text-2xl sm:text-3xl font-black text-slate-800 font-mono mt-1">
                        {{ number_format($totalCabezasVendidas) }} <span class="text-xs font-normal text-slate-500">animales</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Estatus actualizado a Vendido</span>
                </div>

                <!-- Kilos Totales Vendidos -->
                <div class="bg-white p-5 rounded-2xl shadow-xs border border-slate-200">
                    <span class="text-xs font-semibold uppercase text-slate-400 block">Volumen en Báscula de Salida</span>
                    <div class="text-2xl sm:text-3xl font-black text-slate-800 font-mono mt-1">
                        {{ number_format($totalKilosVendidos, 2) }} <span class="text-xs font-normal text-slate-500">kg</span>
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Peso total comercializado</span>
                </div>
            </div>

            <!-- TABLA DE VENTAS REGISTRADAS -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden p-6 space-y-4">
                <h3 class="text-base font-bold text-slate-800">Historial de Ventas y Liquidaciones</h3>

                <div class="overflow-x-auto rounded-xl border border-slate-100">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200 tracking-wider">
                                <th class="p-3.5">Folio</th>
                                <th class="p-3.5">Fecha</th>
                                <th class="p-3.5">Comprador / Destino</th>
                                <th class="p-3.5">Cabezas</th>
                                <th class="p-3.5">Peso Báscula</th>
                                <th class="p-3.5">Modalidad</th>
                                <th class="p-3.5">Monto Total</th>
                                <th class="p-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($ventas as $venta)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-3.5 font-bold font-mono text-emerald-800">
                                        <a href="{{ route('ventas.show', $venta->id) }}" class="hover:underline flex items-center gap-1.5">
                                            <i class="fa-solid fa-receipt text-xs"></i>
                                            {{ $venta->folio_venta }}
                                        </a>
                                    </td>
                                    <td class="p-3.5 text-slate-700">
                                        {{ $venta->fecha_venta->format('d/m/Y') }}
                                    </td>
                                    <td class="p-3.5 text-slate-800 font-semibold">
                                        <div>{{ $venta->cliente_nombre }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">Destino: {{ $venta->destino ?: 'General' }}</div>
                                    </td>
                                    <td class="p-3.5 font-bold text-slate-800 font-mono">
                                        {{ $venta->total_cabezas }}
                                    </td>
                                    <td class="p-3.5 text-slate-700 font-mono">
                                        {{ number_format($venta->peso_total_kg, 2) }} kg
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $venta->modalidad_pago === 'por_kilo' ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ $venta->modalidad_pago === 'por_kilo' ? 'Por Kilo' : 'Precio Fijo' }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 font-black text-slate-900 font-mono text-sm">
                                        {{ formatoPesos($venta->total_venta) }}
                                    </td>
                                    <td class="p-3.5 text-center">
                                        <a href="{{ route('ventas.show', $venta->id) }}"
                                           class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition inline-flex items-center gap-1">
                                            <i class="fa-solid fa-eye text-xs"></i> Liquidación
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">
                                        <i class="fa-solid fa-file-invoice text-3xl mb-2 text-slate-300 block"></i>
                                        Aún no se han registrado ventas de ganado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-2">
                    {{ $ventas->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
