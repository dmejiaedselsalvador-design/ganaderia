@php
    use App\Models\Ganado\FacturaGanado as F;
    $pestanas = [
        'pendientes' => ['Pendientes de liquidar', ($conteos[F::ESTADO_PENDIENTE] ?? 0) + ($conteos[F::ESTADO_PARCIAL] ?? 0), 'fa-hourglass-half'],
        'liquidadas' => ['Liquidadas', $conteos[F::ESTADO_LIQUIDADO] ?? 0, 'fa-circle-check'],
        'anuladas'   => ['Anuladas', $conteos[F::ESTADO_ANULADA] ?? 0, 'fa-ban'],
        'todas'      => ['Todas', $conteos->sum(), 'fa-list'],
    ];
    $colorEstado = [
        F::ESTADO_PENDIENTE => 'bg-amber-100 text-amber-800',
        F::ESTADO_PARCIAL   => 'bg-blue-100 text-blue-800',
        F::ESTADO_LIQUIDADO => 'bg-emerald-100 text-emerald-800',
        F::ESTADO_ANULADA   => 'bg-slate-100 text-slate-600',
    ];
    $hayFiltros = $desde || $hasta || $q !== '';
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Guías de compra" subtitulo="Guías de proveedores, ganado comprado, balances y liquidaciones" seccion="Compras" icono="fa-file-invoice">
            <x-slot name="acciones">
                <x-ui.btn :href="route('proveedores.facturas.crear')" icono="fa-plus">Crear nueva guía</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <!-- Filtros -->
        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                @foreach ($pestanas as $clave => [$texto, $total, $icono])
                    <a href="{{ route('proveedores.facturas.index', array_filter(['estado' => $clave, 'desde' => $desde?->toDateString(), 'hasta' => $hasta?->toDateString(), 'q' => $q])) }}"
                       class="flex items-center gap-2 rounded-xl px-3.5 py-2 transition {{ $estado === $clave ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class="fa-solid {{ $icono }}"></i> {{ $texto }}
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ $estado === $clave ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $total }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="estado" value="{{ $estado }}">
                <div class="min-w-[14rem] flex-1">
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Buscar</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Número de guía o proveedor" class="mt-1 block w-full rounded-xl border-slate-200 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Desde</label>
                    <input type="date" name="desde" value="{{ $desde?->toDateString() }}" class="mt-1 block rounded-xl border-slate-200 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-600">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta?->toDateString() }}" class="mt-1 block rounded-xl border-slate-200 py-2 text-sm">
                </div>
                <x-ui.btn icono="fa-filter" variante="oscuro">Filtrar</x-ui.btn>
                @if ($hayFiltros)
                    <a href="{{ route('proveedores.facturas.index', ['estado' => $estado]) }}" class="pb-2 text-xs font-semibold text-slate-500 hover:text-slate-800"><i class="fa-solid fa-xmark"></i> Quitar filtros</a>
                @endif
            </form>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Proveedor</th>
                            <th class="px-5 py-3">Guía</th>
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Ganado</th>
                            <th class="px-5 py-3 text-right">Monto de compra</th>
                            <th class="px-5 py-3 text-right">Saldo</th>
                            <th class="px-5 py-3">Estatus</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($proveedores as $guia)
                            @php $saldo = $guia->saldo_pendiente; $liquidada = $guia->estado === F::ESTADO_LIQUIDADO; @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-4 align-middle">
                                    <p class="min-w-[11rem] font-bold text-slate-900">{{ $guia->proveedor->nombreProveedor ?? 'N/D' }}</p>
                                    <p class="text-xs text-slate-500">
                                        @if ($guia->proveedor?->nombreContacto){{ $guia->proveedor->nombreContacto }} · @endif
                                        <i class="fa-solid fa-phone text-[10px]"></i> {{ $guia->proveedor->telefono ?? 'Sin teléfono' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex items-center gap-2">
                                        <span class="whitespace-nowrap font-mono font-semibold text-slate-800">{{ $guia->numeroFactura ?: 'S/N' }}</span>
                                        <form method="POST" action="{{ route('proveedores.facturas.numero', $guia->id) }}"
                                              data-prompt="Número o nombre de la guía" data-prompt-valor="{{ $guia->numeroFactura }}" data-prompt-campo="numero"
                                              data-prompt-mensaje="Escribe el número de la guía." data-boton="Guardar">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="numero">
                                            <button class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-emerald-700" title="Cambiar el número de la guía"><i class="fa-solid fa-pen text-xs"></i></button>
                                        </form>
                                    </div>
                                    @if ($guia->guia_reemo)<p class="whitespace-nowrap text-[11px] text-slate-400">REEMO {{ $guia->guia_reemo }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">{{ $guia->fechaFactura?->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 align-middle">
                                    <span class="whitespace-nowrap rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">{{ $guia->cantidad_ganado }} cab.</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right align-middle font-mono font-bold text-slate-900">{{ formatoPesos($guia->monto_total_calculado, 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right align-middle">
                                    @if ($liquidada)
                                        <span class="text-xs font-semibold text-emerald-600"><i class="fa-solid fa-check"></i> Pagada</span>
                                    @else
                                        <span class="font-mono font-semibold {{ $saldo > 0 ? 'text-rose-600' : 'text-slate-500' }}">{{ formatoPesos($saldo, 2) }}</span>
                                        @if ($guia->total_adelantos_aplicados > 0)<p class="text-[11px] text-slate-400">Anticipos {{ formatoPesos($guia->total_adelantos_aplicados, 2) }}</p>@endif
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $colorEstado[$guia->estado] ?? 'bg-slate-100 text-slate-600' }}">{{ ucfirst($guia->estado) }}</span>
                                </td>
                                <td class="px-5 py-4 align-middle">
                                    <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                        @unless ($liquidada)
                                            <a href="{{ route('compras.nuevo.ganado', ['factura_id' => $guia->id]) }}" title="Agregar ganado a esta guía"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                                <i class="fa-solid fa-plus"></i> Ganado
                                            </a>
                                        @endunless
                                        <a href="{{ route('proveedores.facturas.liquidar', $guia->id) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold {{ $liquidada ? 'border border-slate-200 text-slate-600 hover:bg-slate-50' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                            <i class="fa-solid {{ $liquidada ? 'fa-file-invoice-dollar' : 'fa-money-bill-wave' }}"></i> {{ $liquidada ? 'Ver liquidación' : 'Liquidar' }}
                                        </a>
                                        <a href="{{ route('compras.costos.edit', $guia->id) }}" title="Costos: flete, comisión, trámites, destare y documentos"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                            <i class="fa-solid fa-truck-ramp-box"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-folder-open mb-2 text-3xl text-slate-300"></i>
                                    <p class="text-sm">No hay guías {{ ['pendientes' => 'pendientes de liquidar', 'liquidadas' => 'liquidadas', 'anuladas' => 'anuladas', 'todas' => ''][$estado] ?? '' }}{{ $hayFiltros ? ' con esos filtros' : '' }}.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($proveedores->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $proveedores->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
