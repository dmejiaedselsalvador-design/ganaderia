<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Rentabilidad por lote</title>
<style>body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#1e293b}h1{font-size:15px;margin:0}table{width:100%;border-collapse:collapse;margin-top:10px}
th{background:#f1f5f9;padding:4px;font-size:8px;text-align:right}th:first-child,td:first-child{text-align:left}td{padding:4px;border-bottom:1px solid #e2e8f0;text-align:right}.neg{color:#e11d48}.pos{color:#059669}</style></head>
<body>
<h1>{{ config('plantas.grupo') }} · Rentabilidad por lote</h1><span style="color:#64748b">Generado {{ now()->format('d/m/Y H:i') }}</span>
<table><thead><tr><th>Lote</th><th>Cab.</th><th>Compra</th><th>Alimento</th><th>Medicinas</th><th>Gastos</th><th>Costo total</th><th>Ingreso</th><th>Utilidad</th><th>GDP</th><th>$/kg</th><th>Conv.</th><th>Mort.%</th></tr></thead><tbody>
@foreach ($lotes as $l)
<tr><td>{{ $l['nombre'] }}</td><td>{{ $l['cabezas'] }}</td><td>{{ number_format($l['compra'], 0) }}</td><td>{{ number_format($l['alimento'], 0) }}</td><td>{{ number_format($l['medicina'], 0) }}</td>
<td>{{ number_format($l['gastos'], 0) }}</td><td>{{ number_format($l['costo_total'], 0) }}</td><td>{{ number_format($l['ingreso'], 0) }}</td>
<td class="{{ $l['utilidad'] >= 0 ? 'pos' : 'neg' }}">{{ number_format($l['utilidad'], 0) }}</td><td>{{ $l['gdp'] ?? '—' }}</td><td>{{ $l['costo_kg'] ?? '—' }}</td><td>{{ $l['conversion'] ?? '—' }}</td><td>{{ $l['mortalidad_pct'] }}</td></tr>
@endforeach
</tbody></table>
<p style="color:#64748b;margin-top:10px">Importes en pesos mexicanos. Utilidad de animales activos = proyectada a precio de mercado vigente.</p>
</body></html>
