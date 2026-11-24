<x-app-layout>
<div class="container mx-auto p-6 max-w-5xl bg-white shadow-xl rounded-xl my-6 border border-slate-100">

    <!-- Formulario único que envuelve la interacción de pago -->
    <form action="{{ route('proveedores.facturas.liquidar.pago', $factura->id) }}" method="POST" id="formLiquidacion">
        @csrf

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
           // $adelantosProveedor = $proveedor->adelantos ?? collect();
            $totalGanadoCalculado = $factura->animales->sum('precioGanadoTotal');
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

        <!-- Tabla de Anticipos con Checkboxes Interactivos -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-3 border-b-2 border-slate-200 pb-2">
                <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">Anticipos Entregados al Proveedor</h2>
                <span class="text-xs text-slate-500 font-medium">Marca o desmarca los anticipos para recalcular la liquidación al instante</span>
            </div>
            <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                <table class="min-w-full bg-white text-left text-sm">
                    <thead class="bg-slate-900 text-white uppercase text-xs tracking-wider">
                        <tr>
                            <th class="py-3 px-4 w-16 text-center">Incluir</th>
                            <th class="py-3 px-4">Concepto / Descripción</th>
                            <th class="py-3 px-4 text-right">Cantidad de Dinero</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($adelantosProveedor as $adelanto)
                            <tr class="hover:bg-slate-50 transition-colors adelanto-row" data-monto="{{ $adelanto->dinero }}">
                                <td class="py-3 px-4 text-center">
                                    <input type="checkbox"
                                           name="adelantos[]"
                                           value="{{ $adelanto->id }}"
                                           checked
                                           class="w-4 h-4 text-emerald-600 bg-slate-100 border-slate-300 rounded focus:ring-emerald-500 checkbox-adelanto">
                                </td>
                                <td class="py-3 px-4 text-slate-700">{{ $adelanto->concepto ?? $adelanto->descripcion ?? 'Anticipo general' }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-slate-800">${{ number_format($adelanto->dinero, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 px-4 text-center text-slate-400 italic">No hay anticipos registrados para este proveedor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100 font-bold text-slate-800 border-t border-slate-200">
                        <tr>
                            <td colspan="2" class="py-2 px-4 text-right text-xs uppercase text-slate-700">Suma Total de Anticipos Seleccionados:</td>
                            <td class="py-2 px-4 text-right text-blue-700" id="footerTotalAdelantos">${{ number_format($adelantosProveedor->sum('dinero'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Resumen Financiero Dinámico -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="p-5 border border-slate-200 rounded-xl bg-slate-50 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-base text-slate-800 mb-4 uppercase tracking-wide border-b border-slate-200 pb-2">Desglose de Operación</h3>
                    <div class="flex justify-between py-2 text-sm">
                        <span class="text-slate-600">Total anticipos seleccionados:</span>
                        <span class="text-blue-600 font-bold" id="resumenTotalAdelantos">${{ number_format($adelantosProveedor->sum('dinero'), 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 text-sm border-t border-slate-200">
                        <span class="text-slate-600">Total valor ganado:</span>
                        <span class="text-red-600 font-bold">-${{ number_format($totalGanadoCalculado, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Caja de resultado dinámica -->
            <div id="cajaResultado" class="p-6 border-2 border-emerald-500 bg-emerald-50/70 rounded-xl shadow-md flex flex-col justify-center text-center transition-all duration-300">

                <h3 id="resultadoTitulo" class="font-bold text-sm uppercase text-emerald-800 tracking-wider mb-1">
                    Total a Liquidar
                </h3>

                <div id="resultadoMonto" class="text-4xl font-black text-emerald-900 my-1">
                    $0.00
                </div>

                <p id="resultadoMensaje" class="text-xs font-bold uppercase tracking-wide mt-1 text-emerald-700">
                    Calculando...
                </p>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="mt-8 pt-4 border-t border-slate-200 flex gap-4">
            <a href="{{ url()->previous() }}" class="px-5 py-2.5 bg-slate-500 hover:bg-slate-600 text-white font-medium rounded-lg transition shadow-sm">Volver</a>
            <button type="submit"
               id="btnGenerarPdf"
               class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition shadow-md flex items-center gap-2">
                Confirmar Pago y Generar PDF
            </button>
        </div>

    </form>
</div>

<!-- Script de Recálculo en Vivo con Checkboxes y SweetAlert -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const totalGanado = {{ $totalGanadoCalculado }};

        const filasAdelantos = document.querySelectorAll('.adelanto-row');
        const footerTotalAdelantos = document.getElementById('footerTotalAdelantos');
        const resumenTotalAdelantos = document.getElementById('resumenTotalAdelantos');
        const cajaResultado = document.getElementById('cajaResultado');
        const resultadoTitulo = document.getElementById('resultadoTitulo');
        const resultadoMonto = document.getElementById('resultadoMonto');
        const resultadoMensaje = document.getElementById('resultadoMensaje');

        function recalcular() {
            let sumaAdelantos = 0;

            filasAdelantos.forEach(fila => {
                const checkbox = fila.querySelector('.checkbox-adelanto');
                if (checkbox && checkbox.checked) {
                    sumaAdelantos += parseFloat(fila.getAttribute('data-monto')) || 0;
                }
            });

            // Actualizar textos de sumas de adelantos
            footerTotalAdelantos.textContent = '$' + sumaAdelantos.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            resumenTotalAdelantos.textContent = '$' + sumaAdelantos.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Cálculo financiero: Diferencia entre Ganado y Adelantos
            let diferencia = totalGanado - sumaAdelantos;
            let montoAbsoluto = Math.abs(diferencia);

            resultadoMonto.textContent = '$' + montoAbsoluto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Cambiar estilos y textos según el resultado
            if (diferencia < 0) {
                // Es Deuda del Proveedor
                cajaResultado.className = "p-6 border-2 border-red-500 bg-red-50/70 rounded-xl shadow-md flex flex-col justify-center text-center transition-all duration-300";
                resultadoTitulo.className = "font-bold text-sm uppercase text-red-800 tracking-wider mb-1";
                resultadoTitulo.textContent = "Deuda del Proveedor";
                resultadoMonto.className = "text-4xl font-black text-red-900 my-1";
                resultadoMensaje.className = "text-xs font-bold uppercase tracking-wide mt-1 text-red-700";
                resultadoMensaje.textContent = "El proveedor tiene un saldo pendiente con la empresa";
            } else if (diferencia > 0) {
                // Pago Pendiente / A favor del proveedor
                cajaResultado.className = "p-6 border-2 border-emerald-500 bg-emerald-50/70 rounded-xl shadow-md flex flex-col justify-center text-center transition-all duration-300";
                resultadoTitulo.className = "font-bold text-sm uppercase text-emerald-800 tracking-wider mb-1";
                resultadoTitulo.textContent = "Total a Liquidar";
                resultadoMonto.className = "text-4xl font-black text-emerald-900 my-1";
                resultadoMensaje.className = "text-xs font-bold uppercase tracking-wide mt-1 text-emerald-700";
                resultadoMensaje.textContent = "A favor del proveedor (Pago pendiente)";
            } else {
                // Cuenta Saldada
                cajaResultado.className = "p-6 border-2 border-blue-500 bg-blue-50/70 rounded-xl shadow-md flex flex-col justify-center text-center transition-all duration-300";
                resultadoTitulo.className = "font-bold text-sm uppercase text-blue-800 tracking-wider mb-1";
                resultadoTitulo.textContent = "Cuenta Saldada";
                resultadoMonto.className = "text-4xl font-black text-blue-900 my-1";
                resultadoMensaje.className = "text-xs font-bold uppercase tracking-wide mt-1 text-blue-700";
                resultadoMensaje.textContent = "Sin adeudos pendientes";
            }
        }

        // Escuchar cambios en los checkboxes de los adelantos para recalcular al vuelo
        document.querySelectorAll('.checkbox-adelanto').forEach(cb => {
            cb.addEventListener('change', recalcular);
        });

        // Ejecutar al cargar la página para inicializar los valores correctos
        recalcular();

        // Manejador del SweetAlert para enviar el formulario con los IDs seleccionados
        document.getElementById('formLiquidacion').addEventListener('submit', function(e) {
            e.preventDefault();
            const formulario = this;

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas confirmar el pago con los anticipos seleccionados y generar el comprobante PDF?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    formulario.submit();
                }
            });
        });
    });
</script>
</x-app-layout>
