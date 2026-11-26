@extends('pdf.layout', ['titulo' => 'Inventario de ganado', 'meta' => ['Al' => now()->format('d/m/Y'), 'Cabezas' => $grupos->flatten()->count()]])

@section('contenido')
    @foreach ($grupos as $corral => $animales)
        <h2>Corral {{ $corral }} · {{ $animales->count() }} cabezas · {{ number_format($animales->sum('ultimoPeso'), 0) }} kg</h2>
        <table class="datos">
            <thead><tr><th>Arete</th><th>SINIIGA</th><th>Categoría</th><th>Raza</th><th>Fierro</th><th>Compra</th><th class="r">Días</th><th class="r">Peso compra</th><th class="r">Peso actual</th></tr></thead>
            <tbody>
                @foreach ($animales as $a)
                    <tr>
                        <td class="mono">{{ $a->areteID }}</td><td>{{ $a->arete_siniiga }}</td><td>{{ $a->categoria }}</td><td>{{ $a->raza }}</td><td>{{ $a->fierro }}</td>
                        <td>{{ $a->fechaCompra?->format('d/m/Y') }}</td>
                        <td class="r">{{ $a->fechaCompra ? (int) abs($a->fechaCompra->diffInDays(now())) : '' }}</td>
                        <td class="r">{{ number_format((float) $a->pesoActual, 1) }}</td><td class="r">{{ number_format((float) $a->ultimoPeso, 1) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
@endsection
