<x-app-layout>
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.header titulo="Cuentas por cobrar" subtitulo="Ventas a crédito con saldo pendiente, ordenadas por vencimiento." icono="fa-hand-holding-dollar">
            <x-slot name="acciones">
                <x-ui.btn :href="route('cobranza.index', ['exportar' => 'csv'])" variante="secundario" icono="fa-file-excel">Exportar Excel</x-ui.btn>
                <x-ui.btn :href="route('clientes.index')" variante="oscuro" icono="fa-address-book">Clientes</x-ui.btn>
            </x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <div class="grid grid-cols-2 gap-4 md:grid-cols-5">
            @foreach (['corriente' => ['Al corriente', 'bg-emerald-50 text-emerald-600'], '1_30' => ['Vencido 1-30 días', 'bg-yellow-50 text-yellow-600'],
                       '31_60' => ['31-60 días', 'bg-orange-50 text-orange-600'], '61_90' => ['61-90 días', 'bg-rose-50 text-rose-600'], 'mas_90' => ['Más de 90 días', 'bg-rose-100 text-rose-700']] as $k => [$t, $c])
                <x-ui.kpi :label="$t" :valor="formatoPesos($antiguedad[$k])" icono="fa-clock" :color="$c" />
            @endforeach
        </div>

        <x-ui.card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2.5">Cliente</th><th class="px-3">Folio</th><th class="px-3">Venta</th><th class="px-3">Vence</th><th class="px-3 text-right">Total</th><th class="px-3 text-right">Saldo</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($ventas as $v)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5 font-semibold text-slate-800">
                                    @if ($v->cliente)<a href="{{ route('clientes.show', $v->cliente) }}" class="hover:text-emerald-700">{{ $v->cliente->nombre }}</a>@else{{ $v->cliente_nombre }}@endif
                                </td>
                                <td class="px-3"><a href="{{ route('ventas.show', $v->id) }}" class="font-mono text-xs font-semibold text-emerald-700 hover:underline">{{ $v->folio_venta }}</a></td>
                                <td class="px-3 text-xs text-slate-500">{{ $v->fecha_venta->format('d/m/Y') }}</td>
                                <td class="px-3 text-xs {{ $v->vencida() ? 'font-bold text-rose-600' : 'text-slate-500' }}">
                                    {{ $v->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                    @if ($v->vencida())<span class="block text-[10px]">vencida hace {{ (int) $v->fecha_vencimiento->diffInDays(now()) }} días</span>@endif
                                </td>
                                <td class="px-3 text-right font-mono text-xs">{{ formatoPesos($v->total_venta) }}</td>
                                <td class="px-3 text-right font-mono font-bold {{ $v->vencida() ? 'text-rose-600' : 'text-amber-600' }}">{{ formatoPesos($v->saldo_pendiente) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-emerald-600"><i class="fa-solid fa-circle-check"></i> No hay saldos pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
