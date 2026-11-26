@extends('pdf.layout', ['titulo' => 'Rentabilidad por lote', 'meta' => ['Al' => now()->format('d/m/Y'), 'Lotes' => count($lotes)]])

@section('contenido')
    <table class="datos">
        <thead><tr>
            <th>Lote</th><th class="r">Cab.</th><th class="r">Compra</th><th class="r">Alimento</th><th class="r">Medicinas</th><th class="r">Gastos</th>
            <th class="r">Costo total</th><th class="r">Ingreso</th><th class="r">Utilidad</th><th class="r">GDP</th><th class="r">$/kg</th><th class="r">Conv.</th><th class="r">Mort. %</th>
        </tr></thead>
        <tbody>
            @foreach ($lotes as $l)
                <tr>
                    <td><strong>{{ $l['nombre'] }}</strong></td><td class="r">{{ $l['cabezas'] }}</td>
                    <td class="r">{{ number_format($l['compra'], 0) }}</td><td class="r">{{ number_format($l['alimento'], 0) }}</td>
                    <td class="r">{{ number_format($l['medicina'], 0) }}</td><td class="r">{{ number_format($l['gastos'], 0) }}</td>
                    <td class="r">{{ number_format($l['costo_total'], 0) }}</td><td class="r">{{ number_format($l['ingreso'], 0) }}</td>
                    <td class="r {{ $l['utilidad'] >= 0 ? 'verde' : 'rojo' }}"><strong>{{ number_format($l['utilidad'], 0) }}</strong></td>
                    <td class="r">{{ $l['gdp'] ?? '—' }}</td><td class="r">{{ $l['costo_kg'] ?? '—' }}</td>
                    <td class="r">{{ $l['conversion'] ?? '—' }}</td><td class="r">{{ $l['mortalidad_pct'] }}</td>
                </tr>
            @endforeach
            @php $col = collect($lotes); @endphp
            <tr class="total">
                <td>Total</td><td class="r">{{ $col->sum('cabezas') }}</td>
                @foreach (['compra', 'alimento', 'medicina', 'gastos', 'costo_total', 'ingreso'] as $k)<td class="r">{{ number_format($col->sum($k), 0) }}</td>@endforeach
                <td class="r {{ $col->sum('utilidad') >= 0 ? 'verde' : 'rojo' }}">{{ number_format($col->sum('utilidad'), 0) }}</td>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>
    <p class="nota" style="margin-top: 10px;">Importes en pesos mexicanos. La utilidad de los animales activos es proyectada con el precio de mercado vigente.</p>
@endsection
