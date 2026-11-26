<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes Flash de Éxito / Error -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl flex items-center justify-between text-emerald-800 text-sm shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-sm shadow-xs">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-circle-exclamation text-rose-600"></i> Atención
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden p-6 space-y-6">

                <!-- ENCABEZADO Y ACCIONES RÁPIDAS -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-cow text-emerald-600"></i> Inventario de Ganado
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Control biométrico, aretes individuales, peso en báscula y seguimiento de venta.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                        @can('ganaderia.ganado')
                            @include('animals._peso-objetivo')
                        @endcan
                        <a href="{{ route('ventas.create') }}"
                           class="flex-1 sm:flex-none bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-cart-shopping"></i> Vender Ganado
                        </a>

                        <a href="{{ route('proveedores.facturas.crear') }}"
                           class="flex-1 sm:flex-none bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-plus"></i> Nueva Compra / Lote
                        </a>
                    </div>
                </div>

                <!-- TABS DE FILTRO POR ESTADO CON CONTADORES EN TIEMPO REAL -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-100 text-xs font-semibold">
                    <a href="{{ route('compras.ganado.index', ['filtro' => 'activos']) }}"
                       class="px-3.5 py-2 rounded-xl transition shrink-0 flex items-center gap-2 {{ $filtro === 'activos' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-check"></i> Activos en Corral
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filtro === 'activos' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $conteos['activos'] }}</span>
                    </a>

                    <a href="{{ route('compras.ganado.index', ['filtro' => 'listos_venta']) }}"
                       class="px-3.5 py-2 rounded-xl transition shrink-0 flex items-center gap-2 {{ $filtro === 'listos_venta' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}">
                        <i class="fa-solid fa-bullseye"></i> 🎯 Listos para Venta
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filtro === 'listos_venta' ? 'bg-white/25 text-white' : 'bg-amber-200 text-amber-900 font-bold' }}">{{ $conteos['listos_venta'] }}</span>
                    </a>

                    <a href="{{ route('compras.ganado.index', ['filtro' => 'vendidos']) }}"
                       class="px-3.5 py-2 rounded-xl transition shrink-0 flex items-center gap-2 {{ $filtro === 'vendidos' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-tag"></i> Vendidos
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filtro === 'vendidos' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $conteos['vendidos'] }}</span>
                    </a>

                    <a href="{{ route('compras.ganado.index', ['filtro' => 'muertos']) }}"
                       class="px-3.5 py-2 rounded-xl transition shrink-0 flex items-center gap-2 {{ $filtro === 'muertos' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid fa-skull"></i> Bajas / Muertos
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filtro === 'muertos' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $conteos['muertos'] }}</span>
                    </a>

                    <a href="{{ route('compras.ganado.index', ['filtro' => 'todos']) }}"
                       class="px-3.5 py-2 rounded-xl transition shrink-0 flex items-center gap-2 {{ $filtro === 'todos' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Todos (Histórico)
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filtro === 'todos' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $conteos['todos'] }}</span>
                    </a>
                </div>

                <!-- BUSCADOR -->
                <form action="{{ route('compras.ganado.index') }}" method="GET" class="flex gap-2">
                    <input type="hidden" name="filtro" value="{{ $filtro }}">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="buscar" value="{{ request('buscar') }}"
                               placeholder="Buscar por arete, raza o categoría..."
                               class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition">
                        Buscar
                    </button>
                    @if(request('buscar'))
                        <a href="{{ route('compras.ganado.index', ['filtro' => $filtro]) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center">
                            Limpiar
                        </a>
                    @endif
                </form>

                <!-- TABLA DE GANADO -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200 tracking-wider">
                                <th class="p-3.5">Arete ID</th>
                                <th class="p-3.5">Categoría / Raza</th>
                                <th class="p-3.5">Corral / Lote</th>
                                <th class="p-3.5">Peso Actual / Objetivo</th>
                                <th class="p-3.5">Costo Compra</th>
                                <th class="p-3.5">Estatus / Alerta</th>
                                <th class="p-3.5 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($ganados as $ganado)
                                <tr class="hover:bg-slate-50/60 transition group">
                                    <!-- Arete -->
                                    <td class="p-3.5 font-bold text-slate-800">
                                        <a href="{{ route('compras.ganado.perfil', ['arete' => $ganado->areteID]) }}"
                                           class="inline-flex items-center gap-2 hover:text-emerald-600 transition">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $ganado->status === 'Activo' ? ($ganado->esta_listo_venta ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500') : ($ganado->status === 'Vendido' ? 'bg-blue-500' : 'bg-rose-500') }}"></span>
                                            <span class="font-mono">{{ $ganado->areteID }}</span>
                                        </a>
                                    </td>

                                    <!-- Categoría / Raza / Sexo -->
                                    <td class="p-3.5 text-slate-700">
                                        <div class="font-semibold">{{ $ganado->categoria }} ({{ $ganado->sexo }})</div>
                                        <div class="text-[11px] text-slate-400">{{ $ganado->raza ?: 'Sin raza especificada' }}</div>
                                    </td>

                                    <!-- Lote / Corral -->
                                    <td class="p-3.5 text-slate-600 text-xs">
                                        {{ $ganado->lote_potrero ?: 'General' }}
                                    </td>

                                    <!-- Peso Actual vs Objetivo -->
                                    <td class="p-3.5 font-medium">
                                        <div class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                                            <span>{{ number_format($ganado->ultimoPeso, 2) }} kg</span>
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            Objetivo: <strong class="text-slate-600">{{ number_format($ganado->peso_objetivo_calculado, 1) }} kg</strong>
                                        </div>
                                    </td>

                                    <!-- Costo de Compra en MXN -->
                                    <td class="p-3.5 font-semibold text-slate-700 font-mono">
                                        {{ formatoPesos($ganado->precioGanadoTotal) }}
                                    </td>

                                    <!-- Estatus y Alerta de Venta -->
                                    <td class="p-3.5">
                                        <div class="flex flex-col gap-1 items-start">
                                            @if($ganado->status === 'Activo')
                                                @if($ganado->esta_listo_venta)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                                                        <i class="fa-solid fa-bullseye text-amber-600"></i> LISTO VENTA
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                        En Desarrollo
                                                    </span>
                                                @endif
                                            @elseif($ganado->status === 'Vendido')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-800">
                                                    Vendido
                                                </span>
                                            @elseif($ganado->status === 'Muerto')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                                    Baja / Muerte
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                                    {{ $ganado->status }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="p-3.5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('compras.ganado.perfil', ['arete' => $ganado->areteID]) }}"
                                               title="Ver expediente operativo"
                                               class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1">
                                                <i class="fa-solid fa-id-card"></i> Expediente
                                            </a>

                                            @if($ganado->puedeOperar())
                                                <a href="{{ route('ventas.create', ['seleccionados' => $ganado->GanadoID]) }}"
                                                   title="Vender este animal"
                                                   class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-semibold text-xs transition flex items-center gap-1">
                                                    <i class="fa-solid fa-tag"></i> Vender
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">
                                        <i class="fa-solid fa-cow text-3xl mb-2 block text-slate-300"></i>
                                        No se encontraron animales con el filtro seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="pt-2">
                    {{ $ganados->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
