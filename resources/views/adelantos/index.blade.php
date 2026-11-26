<x-app-layout>
    @php
        $temaActual = session('tema_usuario', cache('tema_usuario_' . (Auth::id() ?? 'guest'), 'claro'));
    @endphp

    {{-- ── Cabecera ── --}}
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                    <i class="fa-solid fa-hand-holding-dollar text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-semibold leading-tight tema-heading">Anticipos a Proveedores</h1>
                    <p class="text-xs tema-muted">Gestión de préstamos y adelantos financieros · Pesos Mexicanos (MXN)</p>
                </div>
            </div>
            <a href="{{ route('adelantos.crear') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow transition">
                <i class="fa-solid fa-plus text-xs"></i> Nuevo Adelanto
            </a>
        </div>
    </x-slot>

    {{-- ── Flash Messages ── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-500"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('info'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 shadow-sm">
            <i class="fa-solid fa-circle-info text-blue-500"></i>
            <span class="text-sm font-medium">{{ session('info') }}</span>
        </div>
    @endif

    {{-- ── KPI Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Total Entregado --}}
        <div class="tema-card rounded-2xl p-5 shadow-sm border">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider tema-muted">Total Entregado</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-wave text-indigo-500 text-xs"></i>
                </div>
            </div>
            <p class="text-xl font-bold tema-heading truncate">{{ formatoPesos($totalDineroEntregadoMXN) }}</p>
            <p class="text-xs tema-muted mt-1">{{ $totalItems }} adelanto(s) en total</p>
        </div>

        {{-- Saldo Pendiente --}}
        <div class="tema-card rounded-2xl p-5 shadow-sm border">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider tema-muted">Saldo Pendiente</span>
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-amber-500 text-xs"></i>
                </div>
            </div>
            <p class="text-xl font-bold text-amber-600 truncate">{{ formatoPesos($saldoPendienteMXN) }}</p>
            <p class="text-xs tema-muted mt-1">{{ $totalPendientesItems }} ítem(s) pendientes</p>
        </div>

        {{-- Total Liquidado --}}
        <div class="tema-card rounded-2xl p-5 shadow-sm border">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider tema-muted">Total Liquidado</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i>
                </div>
            </div>
            <p class="text-xl font-bold text-emerald-600 truncate">{{ formatoPesos($totalDineroLiquidadoMXN) }}</p>
            <p class="text-xs tema-muted mt-1">Pagos confirmados</p>
        </div>

        {{-- % Recuperado --}}
        <div class="tema-card rounded-2xl p-5 shadow-sm border">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider tema-muted">Recuperación</span>
                <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                    <i class="fa-solid fa-chart-pie text-rose-500 text-xs"></i>
                </div>
            </div>
            @php
                $pct = $totalDineroEntregadoMXN > 0
                    ? round(($totalDineroLiquidadoMXN / $totalDineroEntregadoMXN) * 100, 1)
                    : 0;
            @endphp
            <p class="text-xl font-bold tema-heading">{{ $pct }}%</p>
            <div class="mt-2 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all"
                     style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- ── Filtros ── --}}
    <div class="tema-card rounded-2xl shadow-sm border mb-5">
        <form method="GET" action="{{ route('adelantos.index') }}" class="p-4">
            <div class="flex flex-wrap items-end gap-3">

                {{-- Tabs filtro --}}
                <div class="flex rounded-xl overflow-hidden border border-slate-200 text-xs font-semibold">
                    @foreach(['todos' => 'Todos', 'pendientes' => 'Pendientes', 'liquidados' => 'Liquidados'] as $key => $label)
                        <a href="{{ route('adelantos.index', array_merge(request()->except(['filtro','page']), ['filtro' => $key])) }}"
                           class="px-4 py-2 transition
                               {{ $filtro === $key
                                   ? 'bg-emerald-600 text-white'
                                   : 'tema-card tema-muted hover:bg-slate-100' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Proveedor --}}
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-xs tema-muted mb-1">Proveedor</label>
                    <select name="proveedor_id"
                            class="w-full text-sm rounded-xl border-slate-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 tema-input">
                        <option value="">— Todos —</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ $proveedorId == $prov->id ? 'selected' : '' }}>
                                {{ $prov->nombreProveedor }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buscar --}}
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs tema-muted mb-1">Buscar concepto</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="buscar" value="{{ $buscar }}"
                               placeholder="Préstamo, producto..."
                               class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border-slate-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 tema-input">
                    </div>
                </div>

                {{-- Botones --}}
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold transition shadow-sm">
                        <i class="fa-solid fa-filter mr-1"></i> Filtrar
                    </button>
                    @if($buscar || $proveedorId || $filtro !== 'todos')
                        <a href="{{ route('adelantos.index') }}"
                           class="px-4 py-2 rounded-xl border border-slate-200 tema-muted text-sm hover:bg-slate-100 transition">
                            <i class="fa-solid fa-xmark mr-1"></i> Limpiar
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- ── Tabla de Ítems ── --}}
    <div class="tema-card rounded-2xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y tema-divide">
                <thead>
                    <tr class="tema-thead">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide tema-muted">#ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide tema-muted">Proveedor</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide tema-muted">Fecha</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide tema-muted">Concepto</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide tema-muted">Monto MXN</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide tema-muted">Estatus</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide tema-muted">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y tema-divide">
                    @forelse($adelantos as $adelanto)
                        <tr class="tema-row-hover transition">
                            {{-- ID --}}
                            <td class="px-5 py-3 text-xs font-mono tema-muted">#{{ $adelanto->id }}</td>

                            {{-- Proveedor --}}
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-[10px] font-bold flex-shrink-0">
                                        {{ strtoupper(substr($adelanto->proveedor->nombreProveedor ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium tema-heading leading-tight">
                                            {{ $adelanto->proveedor->nombreProveedor ?? '—' }}
                                        </p>
                                        @if($adelanto->proveedor?->nombreContacto)
                                            <p class="text-xs tema-muted">{{ $adelanto->proveedor->nombreContacto }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Fecha --}}
                            <td class="px-5 py-3 text-sm tema-muted whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($adelanto->date)->format('d/m/Y') }}
                            </td>

                            {{-- Concepto --}}
                            <td class="px-5 py-3 text-sm tema-heading max-w-xs truncate" title="{{ $adelanto->concepto }}">
                                {{ $adelanto->concepto }}
                            </td>

                            {{-- Monto --}}
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-bold
                                    {{ $adelanto->status === 'liquidado' ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ formatoPesos($adelanto->dinero) }}
                                </span>
                            </td>

                            {{-- Estatus --}}
                            <td class="px-5 py-3 text-center">
                                @if($adelanto->status === 'liquidado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Liquidado
                                    </span>
                                @elseif($adelanto->status === 'parcial')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <i class="fa-solid fa-circle-half-stroke text-[10px]"></i> Parcial
                                    </span>
                                    <span class="mt-1 block text-[10px] text-slate-500">Queda {{ formatoPesos($adelanto->saldoDisponible()) }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Pendiente
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($adelanto->status !== 'liquidado')
                                        {{-- Botón Pagar individual (sin ir a la guía) --}}
                                        <form method="POST"
                                              action="{{ route('adelantos.pagar', $adelanto->id) }}"
                                              data-confirm="¿Confirmar el pago de {{ formatoPesos($adelanto->dinero) }} por concepto «{{ $adelanto->concepto }}»?" data-boton="Confirmar pago">
                                            @csrf
                                            <button type="submit"
                                                    title="Liquidar este adelanto"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-sm">
                                                <i class="fa-solid fa-money-bill-wave text-[10px]"></i> Pagar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs tema-muted italic">Pagado</span>
                                    @endif

                                    {{-- Enlace al proveedor --}}
                                    @if($adelanto->proveedor_id)
                                        <a href="{{ route('adelantos.proveedores.index', $adelanto->proveedor_id) }}"
                                           title="Nuevo adelanto a este proveedor"
                                           class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 tema-muted hover:bg-slate-100 transition text-xs">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 tema-muted">
                                    <i class="fa-solid fa-hand-holding-dollar text-4xl opacity-30"></i>
                                    <p class="text-sm font-medium">Sin adelantos registrados</p>
                                    <a href="{{ route('adelantos.crear') }}"
                                       class="mt-1 text-xs text-emerald-600 hover:underline font-semibold">
                                        + Registrar el primero
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- Pie de tabla con totales --}}
                @if($adelantos->count() > 0)
                    <tfoot>
                        <tr class="tema-tfoot">
                            <td colspan="4" class="px-5 py-3 text-xs font-semibold tema-muted text-right">
                                Subtotal mostrado ({{ $adelantos->count() }} de {{ $adelantos->total() }} ítems):
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-sm tema-heading">
                                {{ formatoPesos($adelantos->sum('dinero')) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Paginación --}}
        @if($adelantos->hasPages())
            <div class="px-5 py-4 border-t tema-divide">
                {{ $adelantos->links() }}
            </div>
        @endif
    </div>

    {{-- ── Estilos de temas (clases utilitarias semánticas) ── --}}
    <style>
        /* ── CLARO (default) ── */
        .tema-card     { background:#ffffff; border-color:#e2e8f0; }
        .tema-heading  { color:#1e293b; }
        .tema-muted    { color:#64748b; }
        .tema-input    { background:#f8fafc; color:#1e293b; border-color:#e2e8f0; }
        .tema-divide   { border-color:#f1f5f9; }
        .tema-thead    { background:#f8fafc; }
        .tema-tfoot    { background:#f8fafc; }
        .tema-row-hover:hover { background:#f8fafc; }

        /* ── METAL ── */
        body.tema-metal .tema-card     { background:#e2e8f0; border-color:#94a3b8; }
        body.tema-metal .tema-heading  { color:#0f172a; }
        body.tema-metal .tema-muted    { color:#475569; }
        body.tema-metal .tema-input    { background:#cbd5e1; color:#0f172a; border-color:#94a3b8; }
        body.tema-metal .tema-divide   { border-color:#94a3b8; }
        body.tema-metal .tema-thead    { background:#cbd5e1; }
        body.tema-metal .tema-tfoot    { background:#cbd5e1; }
        body.tema-metal .tema-row-hover:hover { background:#d1d5db; }

        /* ── DARK ── */
        body.tema-dark .tema-card     { background:#1e293b; border-color:#334155; }
        body.tema-dark .tema-heading  { color:#f1f5f9; }
        body.tema-dark .tema-muted    { color:#94a3b8; }
        body.tema-dark .tema-input    { background:#0f172a; color:#f1f5f9; border-color:#334155; }
        body.tema-dark .tema-divide   { border-color:#334155; }
        body.tema-dark .tema-thead    { background:#0f172a; }
        body.tema-dark .tema-tfoot    { background:#0f172a; }
        body.tema-dark .tema-row-hover:hover { background:#263142; }
    </style>

</x-app-layout>
