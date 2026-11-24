<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Liquidación de Proveedor - Ganadería</title>
    <style>
        /* Definir márgenes de la página dejando espacio arriba para la cabecera fija */
        @page {
            margin: 180px 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Cabecera Fija que se repite automáticamente en todas las páginas */
        .page-header {
            position: fixed;
            top: -150px;
            left: 0;
            right: 0;
            height: 130px;
        }

        /* Encabezado Ejecutivo */
        .header-container {
            width: 100%;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            float: left;
            width: 60%;
        }
        .header-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title p {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }
        .header-meta {
            float: right;
            width: 38%;
            text-align: right;
        }
        .header-meta span {
            display: block;
            font-size: 11px;
            color: #475569;
        }
        .header-meta strong {
            font-size: 12px;
            color: #0f172a;
        }
        .clearfix::after { content: ""; clear: both; display: table; }

        /* Tarjeta de Información */
        .info-card {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 14px;
            margin-bottom: 15px;
            border-radius: 0 6px 6px 0;
        }
        .info-grid { width: 100%; display: table; }
        .info-col { display: table-cell; width: 33.33%; vertical-align: top; }
        .info-col span { display: block; font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 2px; }
        .info-col strong { font-size: 12px; color: #0f172a; }

        /* Secciones y Tablas */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin: 18px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            padding: 7px 10px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            page-break-inside: avoid;
        }
        td { color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Estilos de Totales en Tablas (Footer) */
        tfoot tr td {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 12px;
        }

        /* Bloque de Resumen y Liquidación Final */
        .summary-container {
            width: 100%;
            display: table;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .summary-col-left {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        .summary-col-space {
            display: table-cell;
            width: 4%;
        }
        .summary-col-right {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }

        .box-calculations {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
        }
        .box-calculations h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .calc-row {
            width: 100%;
            margin-bottom: 4px;
            font-size: 12px;
        }
        .calc-row span:first-child { float: left; color: #475569; }
        .calc-row span:last-child { float: right; font-weight: bold; }

        .box-final-pay {
            background: #ecfdf5;
            border: 2px solid #059669;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .box-final-pay h4 {
            margin: 0 0 4px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #065f46;
            letter-spacing: 0.5px;
        }
        .final-amount {
            font-size: 24px;
            font-weight: 900;
            color: #047857;
            margin: 4px 0;
        }
        .final-label {
            font-size: 11px;
            color: #047857;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        /* Firmas de Autorización */
        .signatures-container {
            width: 100%;
            display: table;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-box {
            display: table-cell;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            width: 80%;
            margin: 0 auto 6px auto;
        }
        .sig-box p {
            margin: 0;
            font-size: 11px;
            color: #64748b;
        }
        .sig-space { display: table-cell; width: 10%; }
    </style>
</head>
<body>

    <!-- CABECERA FIJA (Se repetirá automáticamente arriba en cada nueva página) -->
    <div class="page-header">
        <div class="header-container clearfix">
            <div class="header-title">
                <h1>Liquidación de Ganado</h1>
                <p>Comprobante Oficial de Compra / Transacción</p>
            </div>
            <div class="header-meta">
                <span>Fecha de Emisión: <strong>{{ date('d/m/Y') }}</strong></span>
                <span style="margin-top: 2px;">Guia N°: <strong>{{ $factura->numeroFactura ?? 'S/N' }}</strong></span>
            </div>
        </div>

        <div class="info-card">
            <div class="info-grid">
                <div class="info-col">
                    <span>Proveedor</span>
                    <strong>{{ $proveedor->nombreContacto ?? 'N/D' }}</strong>
                </div>
                <div class="info-col">
                    <span>Teléfono / Contacto</span>
                    <strong>{{ $proveedor->telefono ?? 'No disponible' }}</strong>
                </div>
                <div class="info-col">
                    <span>Fecha de Guia</span>
                    <strong>{{ $factura->fechaFactura ? $factura->fechaFactura->format('d/m/Y') : 'N/D' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- CUERPO DEL CONTENIDO -->
    <div>
        <!-- Preparación de colecciones por categorías -->
        @php
            $categoriasPosibles = ['Becerro', 'Torete', 'Toro', 'Becerra', 'Vaquilla', 'Vaca'];
            $adelantosProveedor = $proveedor->adelantos ?? collect();
        @endphp

        <!-- Bucle dinámico para mostrar tablas por categoría -->
        @foreach($categoriasPosibles as $cat)
            @php
                $animalesCat = $factura->animales->where('categoria', $cat);
            @endphp

            @if($animalesCat->count() > 0)
                <div class="section-title">Tabla de {{ $cat }}s</div>
                <table>
                    <thead>
                        <tr>
                            <th>Arete ID</th>
                            <th>Peso (Kg)</th>
                            <th class="text-right">Precio Unitario</th>
                            <th class="text-right">Precio Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($animalesCat as $animal)
                            <tr>
                                <td><strong>{{ $animal->areteID }}</strong></td>
                                <td>{{ $animal->ultimoPeso ?? 'N/D' }} kg</td>
                                <td class="text-right">${{ number_format($animal->precioCompra, 2) }}</td>
                                <td class="text-right">${{ number_format($animal->precioGanadoTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right">Promedio {{ $cat }}:</td>
                            <td colspan="2" class="text-right" style="font-weight: normal;">${{ number_format($animalesCat->avg('precioCompra'), 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right">Total {{ $cat }}s:</td>
                            <td class="text-right">${{ number_format($animalesCat->sum('precioGanadoTotal'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endforeach

        <!-- Tabla de Adelantos -->
        <div class="section-title">Conceptos de Adelanto</div>
        <table>
            <thead>
                <tr>
                    <th>Concepto / Descripción</th>
                    <th class="text-right">Cantidad de Dinero</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adelantosProveedor as $adelanto)
                    <tr>
                        <td>{{ $adelanto->concepto ?? $adelanto->descripcion ?? 'Adelanto general' }}</td>
                        <td class="text-right">${{ number_format($adelanto->dinero, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center" style="color: #94a3b8; font-style: italic; padding: 12px;">No hay adelantos registrados para este proveedor.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-right">Suma Adelantos:</td>
                    <td class="text-right">${{ number_format($adelantosProveedor->sum('dinero'), 2) }}</td>
                </tr>
            </tfoot>
        </table>



        <div class="summary-container">
            <div class="summary-col-left">
                <div class="box-calculations">
                    <h4>Desglose de Operación</h4>
                    <div class="calc-row clearfix">
                        <span>Total Anticipos dados:</span>
                        <span style="color: #2563eb;">${{ number_format($totalAdelantos, 2) }}</span>
                    </div>
                    <div class="calc-row clearfix" style="margin-top: 6px;">
                        <span>Total valor ganado:</span>
                        <span style="color: #dc2626;">-${{ number_format($totalGanado, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="summary-col-space"></div>
            <div class="summary-col-right">
                <div class="box-final-pay" style="
                    @if($esDeudaProveedor)
                        background: #fef2f2;
                        border: 2px solid #dc2626;
                    @endif">

                    <h4 style="
                        @if($esDeudaProveedor)
                            color: #991b1b;
                        @endif">
                        @if($esDeudaProveedor) Deuda del Proveedor (Anticipo) @else Total a Liquidar @endif
                    </h4>

                    <div class="final-amount" style="
                        @if($esDeudaProveedor)
                            color: #b91c1c;
                        @endif">
                        ${{ number_format($montoAbsoluto, 2) }}
                    </div>

                <p class="final-label" style="
    @if($esDeudaProveedor)
        color: #991b1b;
    @else
        color: #047857;
    @endif">

    @if($esDeudaProveedor)
        El proveedor tiene un saldo pendiente con la empresa
    @elseif($montoAbsoluto > 0)
        A favor del proveedor (Total a pagar)
    @else
        Cuenta Saldada / Sin adeudos
    @endif
</p>
                </div>
            </div>
        </div>

        <!-- Firmas de Autorización -->
        <div class="signatures-container">
            <div class="sig-box">
                <div class="sig-line"></div>
                <p><strong>Entregue Conforme</strong></p>
                <p>Firma del Proveedor</p>
            </div>
            <div class="sig-space"></div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <p><strong>Recibí / Autorizó</strong></p>
                <p>Control Ganadero</p>
            </div>
        </div>
    </div>

</body>
</html>
