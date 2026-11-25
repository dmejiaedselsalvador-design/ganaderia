<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Inventario de ganado</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#1e293b}h1{font-size:15px;margin:0}h2{font-size:11px;margin:14px 0 4px}table{width:100%;border-collapse:collapse}
th{background:#f1f5f9;padding:4px;font-size:8px;text-align:left}td{padding:4px;border-bottom:1px solid #e2e8f0}.r{text-align:right}</style></head>
<body>
<h1>{{ config('plantas.grupo') }} · Inventario de ganado</h1><span style="color:#64748b">Al {{ now()->format('d/m/Y H:i') }} · {{ $grupos->flatten()->count() }} cabezas activas</span>
@foreach ($grupos as $corral => $animales)
<h2>Corral {{ $corral }} · {{ $animales->count() }} cabezas · {{ number_format($animales->sum('ultimoPeso'), 0) }} kg</h2>
<table><thead><tr><th>Arete</th><th>SINIIGA</th><th>Categoría</th><th>Raza</th><th>Fierro</th><th>Compra</th><th class="r">Días</th><th class="r">Peso ingreso</th><th class="r">Peso actual</th></tr></thead><tbody>
@foreach ($animales as $a)
<tr><td>{{ $a->areteID }}</td><td>{{ $a->arete_siniiga }}</td><td>{{ $a->categoria }}</td><td>{{ $a->raza }}</td><td>{{ $a->fierro }}</td><td>{{ $a->fechaCompra?->format('d/m/Y') }}</td>
<td class="r">{{ $a->fechaCompra ? (int) $a->fechaCompra->diffInDays(now()) : '' }}</td><td class="r">{{ number_format($a->pesoActual, 1) }}</td><td class="r">{{ number_format($a->ultimoPeso, 1) }}</td></tr>
@endforeach
</tbody></table>
@endforeach
</body></html>
