<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><title>Estado de cuenta</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
    h1 { font-size: 16px; margin: 0; } .muted { color: #64748b; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th { background: #f1f5f9; text-align: left; padding: 5px; font-size: 9px; text-transform: uppercase; }
    td { padding: 5px; border-bottom: 1px solid #e2e8f0; } .r { text-align: right; } .tot td { font-weight: bold; border-top: 2px solid #0f172a; }
</style></head>
<body>
    <table style="margin:0"><tr>
        <td style="border:0"><h1>{{ config('plantas.grupo') }} · Ganadería</h1><span class="muted">Estado de cuenta al {{ now()->format('d/m/Y') }}</span></td>
        <td style="border:0" class="r"><strong>{{ $cliente->razon_social ?: $cliente->nombre }}</strong><br>RFC {{ $cliente->rfc ?: '—' }} · C.P. {{ $cliente->codigo_postal ?: '—' }}<br>{{ $cliente->telefono }}</td>
    </tr></table>
    <table>
        <thead><tr><th>Fecha</th><th>Folio</th><th>Condición</th><th>Vence</th><th class="r">Cabezas</th><th class="r">Total</th><th class="r">Pagado</th><th class="r">Saldo</th></tr></thead>
        <tbody>
        @foreach ($ventas->where('estado', 'completada') as $v)
            <tr><td>{{ $v->fecha_venta->format('d/m/Y') }}</td><td>{{ $v->folio_venta }}</td><td>{{ ucfirst($v->condicion_pago) }}</td>
                <td>{{ $v->fecha_vencimiento?->format('d/m/Y') }}</td><td class="r">{{ $v->total_cabezas }}</td>
                <td class="r">{{ formatoPesos($v->total_venta) }}</td><td class="r">{{ formatoPesos($v->totalPagado()) }}</td><td class="r">{{ formatoPesos($v->saldo_pendiente) }}</td></tr>
        @endforeach
        <tr class="tot"><td colspan="5">Totales</td>
            <td class="r">{{ formatoPesos($ventas->where('estado', 'completada')->sum('total_venta')) }}</td>
            <td class="r">{{ formatoPesos($ventas->where('estado', 'completada')->sum(fn ($v) => $v->totalPagado())) }}</td>
            <td class="r">{{ formatoPesos($ventas->where('estado', 'completada')->sum('saldo_pendiente')) }}</td></tr>
        </tbody>
    </table>
</body></html>
