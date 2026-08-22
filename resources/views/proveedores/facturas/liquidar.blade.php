<x-app-layout>
<div class="container mx-auto p-6 max-w-5xl bg-white shadow-xl rounded-xl my-6 border border-slate-100">

    <!-- Encabezado Estilizado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-200 pb-5 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Liquidación de Proveedor</h1>
            <p class="text-sm text-slate-500 mt-1">Revisión y autorización de compra de ganado</p>
        </div>
        <div class="mt-3 md:mt-0 text-left md:text-right bg-slate-50 px-4 py-2 rounded-lg border border-slate-200">
            <span class="block text-xs font-semibold text-slate-500">Fecha de Emisión: <strong class="text-slate-800">{{ date('d/m/Y') }}</strong></span>
            <span class="block text-xs font-semibold text-slate-500 mt-1">Factura N°: <strong class="text-slate-800">{{ $factura->numeroFactura ?? 'S/N' }}</strong></span>
        </div>
    </div>

    <!-- Tarjeta de Datos del Proveedor -->
    <div class="bg-slate-50 border-l-4 border-blue-600 border-y border-r border-slate-200 p-4 mb-8 rounded-r-lg shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Proveedor</span>
                <strong class="text-slate-800 text-base">{{ $proveedor->nombreContacto ?? 'N/D' }}</strong>
            </div>
            <div>
                <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Teléfono / Contacto</span>
                <strong class="text-slate-800 text-base">{{ $proveedor->telefono ?? 'No disponible' }}</strong>
            </div>
            <div>
                <span class="block text-xs uppercase font-bold text-slate-400 tracking-wider">Fecha de Factura</span>
                <strong class="text-slate-800 text-base">{{ $factura->fechaFactura ? $factura->fechaFactura->format('d/m/Y') : 'N/D' }}</strong>
            </div>
        </div>
    </div>

    <!-- Preparación de colecciones por categorías -->
    @php
        $categoriasPosibles = ['Becerro', 'Torete', 'Toro', 'Becerra', 'Vaquilla', 'Vaca'];
        $adelantosProveedor = $proveedor->adelantos ?? collect();
    @endphp

    <!-- Bucle dinámico para mostrar una tabla por cada categoría con animales -->
    @foreach($categoriasPosibles as $cat)
        @php
            $animalesCat = $factura->animales->where('categoria', $cat);
        @endphp

        @if($animalesCat->count() > 0)
            @php
                $esMacho = in_array($cat, ['Becerro', 'Torete', 'Toro']);
                $emojiIcono = $esMacho ? '🐂' : '🐄';
            @endphp
            <div class="mb-8">
                <h2 class="text-lg font-bold text-slate-800 mb-3 uppercase tracking-wide border-b-2 border-slate-200 pb-2">
                    {{ $emojiIcono }} Tabla de {{ $cat }}s
                </h2>
                <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                    <table class="min-w-full bg-white text-left text-sm">
                        <thead class="bg-slate-900 text-white uppercase text-xs tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Arete ID</th>
                                <th class="py-3 px-4">Peso (Kg)</th>
                                <th class="py-3 px-4 text-right">Precio Unitario</th>
                                <th class="py-3 px-4 text-right">Precio Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($animalesCat as $animal)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 font-bold text-slate-700">{{ $animal->areteID }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $animal->ultimoPeso ?? 'N/D' }} kg</td>
                                    <td class="py-3 px-4 text-right text-slate-600">${{ number_format($animal->precioCompra, 2) }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-slate-800">${{ number_format($animal->precioGanadoTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-100 font-bold text-slate-800 border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="py-2 px-4 text-right text-xs uppercase text-slate-500">Promedio {{ $cat }}:</td>
                                <td colspan="2" class="py-2 px-4 text-right font-normal text-slate-700">${{ number_format($animalesCat->avg('precioCompra'), 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="py-2 px-4 text-right text-xs uppercase text-slate-700">Total {{ $cat }}s:</td>
                                <td class="py-2 px-4 text-right text-emerald-700">${{ number_format($animalesCat->sum('precioGanadoTotal'), 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Tabla de Anticipos / Dinero Entregado al Proveedor -->
    <div class="mb-8">
        <h2 class="text-lg font-bold text-slate-800 mb-3 uppercase tracking-wide border-b-2 border-slate-200 pb-2">Anticipos Entregados al Proveedor</h2>
        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
            <table class="min-w-full bg-white text-left text-sm">
                <thead class="bg-slate-900 text-white uppercase text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Concepto / Descripción</th>
                        <th class="py-3 px-4 text-right">Cantidad de Dinero</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($adelantosProveedor as $adelanto)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 text-slate-700">{{ $adelanto->concepto ?? $adelanto->descripcion ?? 'Anticipo general' }}</td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-800">${{ number_format($adelanto->dinero, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="py-4 px-4 text-center text-slate-400 italic">No hay anticipos registrados para este proveedor.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold text-slate-800 border-t border-slate-200">
                    <tr>
                        <td class="py-2 px-4 text-right text-xs uppercase text-slate-700">Suma Total de Anticipos:</td>
                        <td class="py-2 px-4 text-right text-blue-700">${{ number_format($adelantosProveedor->sum('dinero'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Resumen Financiero -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="p-5 border border-slate-200 rounded-xl bg-slate-50 shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-base text-slate-800 mb-4 uppercase tracking-wide border-b border-slate-200 pb-2">Desglose de Operación</h3>
            <div class="flex justify-between py-2 text-sm">
                <span class="text-slate-600">Total anticipos entregados:</span>
                <span class="text-blue-600 font-bold">${{ number_format($totalAdelantos, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 text-sm border-t border-slate-200">
                <span class="text-slate-600">Total valor ganado:</span>
                <span class="text-red-600 font-bold">-${{ number_format($totalGanado, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Caja de resultado basada en $esDeudaProveedor -->
    <div class="p-6 border-2 {{ $esDeudaProveedor ? 'border-red-500 bg-red-50/70' : 'border-emerald-500 bg-emerald-50/70' }} rounded-xl shadow-md flex flex-col justify-center text-center">

        <h3 class="font-bold text-sm uppercase {{ $esDeudaProveedor ? 'text-red-800' : 'text-emerald-800' }} tracking-wider mb-1">
            {{ $esDeudaProveedor ? 'Deuda del Proveedor' : 'Total a Liquidar' }}
        </h3>

        <div class="text-4xl font-black {{ $esDeudaProveedor ? 'text-red-900' : 'text-emerald-900' }} my-1">
            ${{ number_format($montoAbsoluto, 2) }}
        </div>

        <p class="text-xs font-bold uppercase tracking-wide mt-1 {{ $esDeudaProveedor ? 'text-red-700' : 'text-emerald-700' }}">
            @if($esDeudaProveedor)
                El proveedor tiene un saldo pendiente con la empresa
            @elseif($montoAbsoluto > 0)
                A favor del proveedor (Pago pendiente)
            @else
                Cuenta Saldada / Sin adeudos
            @endif
        </p>
    </div>
</div>

    <!-- Botones de Acción -->
    <div class="mt-8 pt-4 border-t border-slate-200 flex gap-4">
        <a href="{{ url()->previous() }}" class="px-5 py-2.5 bg-slate-500 hover:bg-slate-600 text-white font-medium rounded-lg transition shadow-sm">Volver</a>
        <a href="{{ route('proveedores.facturas.liquidar.generarPdf', $factura->id) }}"
           id="btnGenerarPdf"
           class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition shadow-md flex items-center gap-2">
            Confirmar Pago y Generar PDF
        </a>
    </div>

</div>

<!-- Script de SweetAlert2 -->
<script>
    document.getElementById('btnGenerarPdf').addEventListener('click', function(e) {
        e.preventDefault();
        const urlPdf = this.getAttribute('href');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas confirmar el pago y generar el comprobante PDF?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(urlPdf, '_blank');
            }
        });
    });
</script>
</x-app-layout>
