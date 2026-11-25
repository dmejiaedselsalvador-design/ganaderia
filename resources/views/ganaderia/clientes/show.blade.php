@php
    $completadas = $ventas->where('estado', 'completada');
    $saldo = $completadas->sum('saldo_pendiente');
    $vencido = $completadas->filter->vencida()->sum('saldo_pendiente');
@endphp
<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header :titulo="$cliente->nombre" :subtitulo="trim(($cliente->razon_social ?? '') . ($cliente->rfc ? ' · RFC ' . $cliente->rfc : ''), ' ·')" seccion="Estado de cuenta" icono="fa-file-invoice-dollar">
            <x-slot name="acciones">
                <x-ui.btn :href="route('clientes.show', [$cliente, 'exportar' => 'pdf'])" variante="secundario" icono="fa-file-pdf">PDF</x-ui.btn>
                <x-ui.btn :href="route('clientes.show', [$cliente, 'exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Excel</x-ui.btn>
                <x-ui.btn :href="route('clientes.edit', $cliente)" variante="oscuro" icono="fa-pen-to-square">Editar</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-ui.kpi label="Total comprado" :valor="formatoPesosCorto($completadas->sum('total_venta'))" :nota="$completadas->count() . ' ventas · ' . $completadas->sum('total_cabezas') . ' cabezas'" icono="fa-cart-shopping" />
            <x-ui.kpi label="Saldo pendiente" :valor="formatoPesos($saldo)" icono="fa-sack-dollar" color="bg-amber-50 text-amber-600" :valorClass="$saldo > 0 ? 'text-amber-600' : 'text-slate-900'" />
            <x-ui.kpi label="Saldo vencido" :valor="formatoPesos($vencido)" icono="fa-triangle-exclamation" color="bg-rose-50 text-rose-600" :valorClass="$vencido > 0 ? 'text-rose-600' : 'text-slate-900'" />
            <x-ui.kpi label="Crédito disponible" :valor="$cliente->creditoDisponible() !== null ? formatoPesos($cliente->creditoDisponible()) : 'Sin límite'" :nota="$cliente->dias_credito ? $cliente->dias_credito . ' días de crédito' : 'Solo contado'" icono="fa-credit-card" color="bg-blue-50 text-blue-600" />
        </div>

        <x-ui.card titulo="Ventas y pagos" :padding="false">
            <div class="divide-y divide-slate-100">
                @forelse ($ventas as $v)
                    <div class="px-5 py-4 {{ $v->estado !== 'completada' ? 'opacity-50' : '' }}" x-data="{ abrir: false }">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="min-w-[10rem] flex-1">
                                <a href="{{ route('ventas.show', $v->id) }}" class="font-mono font-bold text-slate-800 hover:text-emerald-700">{{ $v->folio_venta }}</a>
                                <p class="text-xs text-slate-500">{{ $v->fecha_venta->format('d/m/Y') }} · {{ $v->total_cabezas }} cab. · {{ $v->condicion_pago === 'credito' ? 'Crédito, vence ' . $v->fecha_vencimiento?->format('d/m/Y') : 'Contado' }}</p>
                            </div>
                            <div class="text-right text-xs"><p class="text-slate-400">Total</p><p class="font-mono font-semibold text-slate-700">{{ formatoPesos($v->total_venta) }}</p></div>
                            <div class="text-right text-xs"><p class="text-slate-400">Pagado</p><p class="font-mono font-semibold text-emerald-700">{{ formatoPesos($v->totalPagado()) }}</p></div>
                            <div class="text-right text-xs"><p class="text-slate-400">Saldo</p><p class="font-mono font-bold {{ $v->vencida() ? 'text-rose-600' : ((float) $v->saldo_pendiente > 0 ? 'text-amber-600' : 'text-slate-400') }}">{{ formatoPesos($v->saldo_pendiente) }}</p></div>
                            @if ((float) $v->saldo_pendiente > 0 && $v->estado === 'completada')
                                <button type="button" @click="abrir = !abrir" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"><i class="fa-solid fa-plus"></i> Abono</button>
                            @endif
                        </div>
                        @if ($v->abonos->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($v->abonos as $ab)
                                    <span class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1 text-[11px] text-slate-600 {{ $ab->estado !== 'vigente' ? 'line-through opacity-60' : '' }}">
                                        {{ $ab->fecha->format('d/m') }} · {{ $ab->forma_pago }} · <strong class="font-mono">{{ formatoPesos($ab->monto) }}</strong>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <form x-show="abrir" x-cloak method="POST" action="{{ route('ventas.abonar', $v) }}" class="mt-3 grid gap-3 rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 sm:grid-cols-5">
                            @csrf
                            <x-ui.field label="Monto" name="monto" type="number" step="0.01" :value="$v->saldo_pendiente" required />
                            <x-ui.field label="Fecha" name="fecha" type="date" :value="date('Y-m-d')" required />
                            <x-ui.field label="Forma" name="forma_pago" type="select" :options="array_combine(\App\Models\Ganaderia\AbonoVenta::FORMAS_PAGO, \App\Models\Ganaderia\AbonoVenta::FORMAS_PAGO)" :placeholder="false" required />
                            <x-ui.field label="Referencia" name="referencia" />
                            <div class="flex items-end"><x-ui.btn icono="fa-check">Registrar</x-ui.btn></div>
                        </form>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-slate-400">Este cliente aún no tiene ventas.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
