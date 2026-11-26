@php
    $categorias = ['Becerro', 'Torete', 'Toro', 'Becerra', 'Vaquilla', 'Vaca'];
    $plural = fn ($c) => $c . 's';
    $liquidada = $factura->estado === \App\Models\Ganado\FacturaGanado::ESTADO_LIQUIDADO;
    $kgTotal = (float) $factura->animales->sum('pesoActual');
    $descontado = min($totalAdelantos, $totalGanado);
    $sobrante = round($totalAdelantos - $descontado, 2);
    $resultado = round($totalGanado - $descontado, 2);
    $meta = ['Guía' => $factura->numeroFactura ?: 'S/N', 'REEMO' => $factura->guia_reemo ?: 'S/N', 'Emisión' => now()->format('d/m/Y')];
@endphp
@extends('pdf.layout', ['titulo' => 'Liquidación de ganado', 'meta' => $meta])

@section('contenido')
    <table class="ficha">
        <tr>
            <td>
                <span class="etiqueta">Proveedor</span>
                <span class="valor">{{ $proveedor->nombreProveedor ?? 'N/D' }}</span>
                @if ($proveedor?->razon_social)<br><span class="nota">{{ $proveedor->razon_social }}</span>@endif
            </td>
            <td>
                <span class="etiqueta">Contacto</span>
                <span class="valor">{{ $proveedor->nombreContacto ?: '—' }}</span>
                <br><span class="nota">Tel. {{ $proveedor->telefono ?: 'sin teléfono' }}</span>
            </td>
            <td>
                <span class="etiqueta">Fecha de compra</span>
                <span class="valor">{{ $factura->fechaFactura?->format('d/m/Y') ?? 'N/D' }}</span>
                @if ($factura->upp_origen)<br><span class="nota">UPP origen {{ $factura->upp_origen }}</span>@endif
            </td>
            <td>
                <span class="etiqueta">Ganado</span>
                <span class="valor">{{ $factura->animales->count() }} cabezas</span>
                <br><span class="nota">{{ number_format($kgTotal, 1) }} kg de compra</span>
            </td>
        </tr>
    </table>

    @foreach ($categorias as $cat)
        @php $animalesCat = $factura->animales->where('categoria', $cat); @endphp
        @continue($animalesCat->isEmpty())
        @php
            $kg = (float) $animalesCat->sum('pesoActual');
            $importe = (float) $animalesCat->sum('precioGanadoTotal');
        @endphp
        <h2>{{ $plural($cat) }} ({{ $animalesCat->count() }})</h2>
        <table class="datos">
            <thead>
                <tr><th style="width: 34%;">Arete</th><th class="r">Peso de compra</th><th class="r">Precio por kg</th><th class="r">Importe</th></tr>
            </thead>
            <tbody>
                @foreach ($animalesCat as $a)
                    <tr>
                        <td class="mono">{{ $a->areteID }}</td>
                        <td class="r">{{ number_format((float) $a->pesoActual, 1) }} kg</td>
                        <td class="r">${{ number_format((float) $a->precioCompra, 2) }}</td>
                        <td class="r">${{ number_format($a->precioGanadoTotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="promedio">
                    <td>Promedio</td>
                    <td class="r">{{ number_format($kg / $animalesCat->count(), 1) }} kg</td>
                    <td class="r">${{ number_format($kg > 0 ? $importe / $kg : 0, 2) }}</td>
                    <td></td>
                </tr>
                <tr class="total">
                    <td>Total {{ strtolower($plural($cat)) }}</td>
                    <td class="r">{{ number_format($kg, 1) }} kg</td>
                    <td></td>
                    <td class="r">${{ number_format($importe, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <h2>{{ $liquidada ? 'Anticipos aplicados' : 'Anticipos a descontar' }}</h2>
    <table class="datos">
        <thead><tr><th>Concepto</th><th class="r" style="width: 25%;">Monto</th></tr></thead>
        <tbody>
            @forelse ($adelantosProveedor as $ad)
                <tr><td>{{ $ad->concepto ?? 'Anticipo' }}</td><td class="r">${{ number_format($ad->monto_aplicado ?? $ad->dinero, 2) }}</td></tr>
            @empty
                <tr><td colspan="2" class="gris">Sin anticipos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="resumen">
        <tr>
            <td style="width: 55%; padding-right: 14px;">
                <table class="cuenta">
                    <tr><td>Valor del ganado comprado</td><td class="r">${{ number_format($totalGanado, 2) }}</td></tr>
                    <tr><td>Menos anticipos</td><td class="r azul">−${{ number_format($descontado, 2) }}</td></tr>
                    <tr class="resultado"><td>Resultado</td><td class="r">{{ $resultado < 0 ? '−' : '' }}${{ number_format(abs($resultado), 2) }}</td></tr>
                </table>
                @if ($sobrante > 0)<p class="nota" style="margin-top: 6px;">Sobran ${{ number_format($sobrante, 2) }} de anticipo a cuenta de la próxima compra.</p>@endif
                @if ($factura->costosAdicionales() > 0)
                    <p class="nota" style="margin-top: 6px;">Flete, comisión y trámites (${{ number_format($factura->costosAdicionales(), 2) }}) se liquidan por separado.</p>
                @endif
            </td>
            <td style="width: 45%;">
                <div class="destacado {{ $resultado < 0 ? 'rojo-caja' : '' }}">
                    <div class="rotulo">
                        @if ($resultado > 0) {{ $liquidada ? 'Pagado al proveedor' : 'Total a pagar al proveedor' }}
                        @elseif ($resultado < 0) Saldo a favor de la empresa
                        @else Cuenta saldada @endif
                    </div>
                    <div class="monto">${{ number_format(abs($resultado), 2) }}</div>
                    <div class="nota">{{ $liquidada ? 'Liquidación cerrada' : 'Pendiente de pago' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="firmas">
        <tr>
            <td>
                <div class="linea"></div>
                <div class="firma-nombre">{{ $proveedor->nombreContacto ?: ($proveedor->nombreProveedor ?? 'Proveedor') }}</div>
                <div class="firma-rol">Entregué conforme · Proveedor</div>
            </td>
            <td class="espacio"></td>
            <td>
                <div class="linea"></div>
                <div class="firma-nombre">{{ auth()->user()?->name ?? 'Control ganadero' }}</div>
                <div class="firma-rol">Recibí y autoricé · {{ \App\Support\Plantas::info('ganaderia')['marca'] ?? config('plantas.grupo') }}</div>
            </td>
        </tr>
    </table>
@endsection
