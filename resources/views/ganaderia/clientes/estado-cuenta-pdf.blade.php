@php
    $vigentes = $ventas->where('estado', 'completada');
    $saldo = (float) $vigentes->sum('saldo_pendiente');
@endphp
@extends('pdf.layout', ['titulo' => 'Estado de cuenta', 'meta' => ['Cliente' => $cliente->nombre, 'Al' => now()->format('d/m/Y')]])

@section('contenido')
    <table class="ficha">
        <tr>
            <td style="width: 40%;"><span class="etiqueta">Cliente</span><span class="valor">{{ $cliente->razon_social ?: $cliente->nombre }}</span></td>
            <td><span class="etiqueta">RFC</span><span class="valor">{{ $cliente->rfc ?: '—' }}</span><br><span class="nota">C.P. {{ $cliente->codigo_postal ?: '—' }}</span></td>
            <td><span class="etiqueta">Teléfono</span><span class="valor">{{ $cliente->telefono ?: '—' }}</span></td>
            <td><span class="etiqueta">Crédito</span><span class="valor">{{ $cliente->dias_credito ? $cliente->dias_credito . ' días' : 'Contado' }}</span>
                @if ((float) $cliente->limite_credito > 0)<br><span class="nota">Límite ${{ number_format((float) $cliente->limite_credito, 2) }}</span>@endif</td>
        </tr>
    </table>

    <h2>Movimientos</h2>
    <table class="datos">
        <thead><tr><th>Fecha</th><th>Folio</th><th>Condición</th><th>Vence</th><th class="r">Cabezas</th><th class="r">Total</th><th class="r">Pagado</th><th class="r">Saldo</th></tr></thead>
        <tbody>
            @forelse ($vigentes as $v)
                <tr>
                    <td>{{ $v->fecha_venta->format('d/m/Y') }}</td>
                    <td class="mono">{{ $v->folio_venta }}</td>
                    <td>{{ ucfirst($v->condicion_pago) }}</td>
                    <td class="{{ $v->vencida() ? 'rojo' : '' }}">{{ $v->fecha_vencimiento?->format('d/m/Y') }}{{ $v->vencida() ? ' (vencida)' : '' }}</td>
                    <td class="r">{{ $v->total_cabezas }}</td>
                    <td class="r">${{ number_format((float) $v->total_venta, 2) }}</td>
                    <td class="r">${{ number_format($v->totalPagado(), 2) }}</td>
                    <td class="r">${{ number_format((float) $v->saldo_pendiente, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="gris">Sin ventas registradas.</td></tr>
            @endforelse
            <tr class="total">
                <td colspan="5">Totales</td>
                <td class="r">${{ number_format((float) $vigentes->sum('total_venta'), 2) }}</td>
                <td class="r">${{ number_format((float) $vigentes->sum(fn ($v) => $v->totalPagado()), 2) }}</td>
                <td class="r">${{ number_format($saldo, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="resumen"><tr>
        <td style="width: 55%;"></td>
        <td style="width: 45%;">
            <div class="destacado {{ $saldo > 0 ? 'rojo-caja' : '' }}">
                <div class="rotulo">{{ $saldo > 0 ? 'Saldo pendiente' : 'Cuenta al corriente' }}</div>
                <div class="monto">${{ number_format($saldo, 2) }}</div>
                <div class="nota">Importes en pesos mexicanos</div>
            </div>
        </td>
    </tr></table>
@endsection
