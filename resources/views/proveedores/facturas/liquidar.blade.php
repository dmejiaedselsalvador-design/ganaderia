@php
    $categoriasPosibles = ['Becerro', 'Torete', 'Toro', 'Becerra', 'Vaquilla', 'Vaca'];
    $totalGanadoCalculado = round((float) $factura->animales->sum('precioGanadoTotal'), 2);
    $totalKg = (float) $factura->animales->sum('pesoActual');
    $costosExtra = $factura->costosAdicionales();
    $plural = fn ($cat) => $cat . (str_ends_with($cat, 'o') || str_ends_with($cat, 'a') ? 's' : 'es');
@endphp
<x-app-layout>
<div class="mx-auto my-6 max-w-5xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

    <form action="{{ route('proveedores.facturas.liquidar.pago', $factura->id) }}" method="POST" id="formLiquidacion">
        @csrf

        <!-- Encabezado -->
        <div class="mb-6 flex flex-col items-start justify-between gap-3 border-b border-slate-200 pb-5 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Liquidación de proveedor</h1>
                <p class="mt-1 text-sm text-slate-500">Revisión y autorización del pago de la compra de ganado</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-left text-xs font-semibold text-slate-500 md:text-right">
                <span class="block">Guía: <strong class="font-mono text-slate-800">{{ $factura->numeroFactura ?? 'S/N' }}</strong></span>
                <span class="mt-1 block">REEMO: <strong class="font-mono text-slate-800">{{ $factura->guia_reemo ?: 'Sin capturar' }}</strong>
                    @if (! $factura->guia_reemo && ! $liquidada)<a href="{{ route('compras.costos.edit', $factura->id) }}" class="ml-1 text-emerald-600 hover:underline">capturar</a>@endif
                </span>
                <span class="mt-1 block">Fecha de emisión: <strong class="text-slate-800">{{ now()->format('d/m/Y') }}</strong></span>
            </div>
        </div>

        @if ($liquidada)
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <span><i class="fa-solid fa-circle-check"></i> Esta guía <strong>ya fue liquidada</strong> el {{ $factura->updated_at?->format('d/m/Y') }}. Solo se muestra para consulta.</span>
                <a href="{{ route('proveedores.facturas.liquidar.generarPdf', $factura->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                    <i class="fa-solid fa-file-pdf"></i> Descargar comprobante
                </a>
            </div>
        @endif

        <!-- Datos del proveedor -->
        <div class="mb-8 grid grid-cols-1 gap-4 rounded-xl border border-l-4 border-slate-200 border-l-blue-600 bg-slate-50 p-4 md:grid-cols-4">
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Proveedor</span>
                <strong class="text-base text-slate-800">{{ $proveedor->nombreProveedor ?? 'N/D' }}</strong>
                @if ($proveedor?->razon_social)<span class="block text-xs text-slate-500">{{ $proveedor->razon_social }}</span>@endif
            </div>
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Contacto</span>
                <strong class="text-base text-slate-800">{{ $proveedor->nombreContacto ?: '—' }}</strong>
                <span class="block text-xs text-slate-500"><i class="fa-solid fa-phone text-[10px]"></i> {{ $proveedor->telefono ?: 'Sin teléfono' }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Fecha de compra</span>
                <strong class="text-base text-slate-800">{{ $factura->fechaFactura?->format('d/m/Y') ?? 'N/D' }}</strong>
                @if ($factura->upp_origen)<span class="block text-xs text-slate-500">UPP origen {{ $factura->upp_origen }}</span>@endif
            </div>
            <div>
                <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Ganado</span>
                <strong class="text-base text-slate-800">{{ $factura->animales->count() }} cabezas</strong>
                <span class="block text-xs text-slate-500">{{ number_format($totalKg, 1) }} kg en total</span>
            </div>
        </div>

        <!-- Una tabla por categoría -->
        @foreach ($categoriasPosibles as $cat)
            @php $animalesCat = $factura->animales->where('categoria', $cat); @endphp
            @continue($animalesCat->isEmpty())
            @php
                $kgCat = (float) $animalesCat->sum('pesoActual');
                $totalCat = (float) $animalesCat->sum('precioGanadoTotal');
                $precioProm = $kgCat > 0 ? $totalCat / $kgCat : 0;          // precio promedio ponderado por kg
                $pesoProm = $kgCat / $animalesCat->count();
            @endphp
            <div class="mb-8">
                <h2 class="mb-3 flex items-center gap-2 border-b-2 border-slate-200 pb-2 text-base font-bold uppercase tracking-wide text-slate-800">
                    <i class="fa-solid {{ in_array($cat, \App\Models\Ganado\Ganado::CATEGORIAS_MACHOS, true) ? 'fa-mars text-blue-500' : 'fa-venus text-rose-500' }}"></i>
                    {{ $plural($cat) }} <span class="text-sm font-semibold normal-case text-slate-400">({{ $animalesCat->count() }})</span>
                </h2>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Arete</th>
                                <th class="px-4 py-3 text-right">Peso de compra</th>
                                <th class="px-4 py-3 text-right">Precio por kg</th>
                                <th class="px-4 py-3 text-right">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($animalesCat as $animal)
                                <tr>
                                    <td class="px-4 py-2.5 font-mono font-semibold text-slate-700">{{ $animal->areteID }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">{{ number_format((float) $animal->pesoActual, 1) }} kg</td>
                                    <td class="px-4 py-2.5 text-right text-slate-600">${{ number_format((float) $animal->precioCompra, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-slate-800">${{ number_format($animal->precioGanadoTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200 bg-slate-50">
                            {{-- Promedios debajo de su propia columna; el total aparte, sin mezclarse --}}
                            <tr class="text-xs text-slate-500">
                                <td class="px-4 py-2 font-semibold uppercase tracking-wide">Promedio</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">{{ number_format($pesoProm, 1) }} kg</td>
                                <td class="px-4 py-2 text-right font-semibold text-slate-700">${{ number_format($precioProm, 2) }}</td>
                                <td class="px-4 py-2"></td>
                            </tr>
                            <tr class="font-bold">
                                <td class="px-4 py-2.5 text-xs uppercase tracking-wide text-slate-700">Total {{ $plural($cat) }}</td>
                                <td class="px-4 py-2.5 text-right text-slate-700">{{ number_format($kgCat, 1) }} kg</td>
                                <td class="px-4 py-2.5"></td>
                                <td class="px-4 py-2.5 text-right text-emerald-700">${{ number_format($totalCat, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        <!-- Anticipos -->
        <div class="mb-8">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b-2 border-slate-200 pb-2">
                <h2 class="text-base font-bold uppercase tracking-wide text-slate-800">{{ $liquidada ? 'Anticipos aplicados' : 'Anticipos entregados al proveedor' }}</h2>
                @unless ($liquidada)<span class="text-xs text-slate-500">Marca o desmarca los anticipos que se descuentan en este pago</span>@endunless
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            @unless ($liquidada)<th class="w-20 px-4 py-3 text-center">Descontar</th>@endunless
                            <th class="px-4 py-3">Concepto</th>
                            <th class="px-4 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($adelantosProveedor as $adelanto)
                            @php $monto = $adelanto->monto_aplicado ?? $adelanto->dinero; @endphp
                            <tr class="adelanto-row" data-monto="{{ $monto }}">
                                @unless ($liquidada)
                                    <td class="px-4 py-2.5 text-center">
                                        <input type="checkbox" name="adelantos[]" value="{{ $adelanto->id }}" checked class="checkbox-adelanto h-4 w-4 rounded border-slate-300 text-emerald-600">
                                    </td>
                                @endunless
                                <td class="px-4 py-2.5 text-slate-700">{{ $adelanto->concepto ?? $adelanto->descripcion ?? 'Anticipo' }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-slate-800">${{ number_format((float) $monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-4 text-center italic text-slate-400">{{ $liquidada ? 'No se aplicaron anticipos.' : 'Este proveedor no tiene anticipos pendientes.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resumen: en el orden en que se hace la cuenta -->
        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                <h3 class="mb-3 border-b border-slate-200 pb-2 text-sm font-bold uppercase tracking-wide text-slate-800">Cuenta</h3>
                <div class="flex justify-between py-2 text-sm">
                    <span class="text-slate-600">Valor del ganado comprado</span>
                    <span class="font-mono font-bold text-slate-800">${{ number_format($totalGanadoCalculado, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 py-2 text-sm">
                    <span class="text-slate-600">Menos anticipos {{ $liquidada ? 'aplicados' : 'a descontar' }}</span>
                    <span class="font-mono font-bold text-blue-600" id="resumenTotalAdelantos">−${{ number_format($totalAdelantos, 2) }}</span>
                </div>
                <div class="flex justify-between border-t-2 border-slate-300 py-2 text-sm">
                    <span class="font-bold text-slate-800">Resultado</span>
                    <span class="font-mono font-black text-slate-900" id="resumenResultado">—</span>
                </div>
                <p id="notaSobrante" class="hidden rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-800"></p>
                @if ($costosExtra > 0)
                    <p class="mt-2 text-[11px] text-slate-400"><i class="fa-solid fa-circle-info"></i> Flete, comisión y trámites (${{ number_format($costosExtra, 2) }}) no se incluyen: se pagan aparte y se suman al costo del ganado.</p>
                @endif
            </div>

            <div id="cajaResultado" class="flex flex-col justify-center rounded-xl border-2 border-emerald-500 bg-emerald-50 p-6 text-center transition-all duration-300">
                <h3 id="resultadoTitulo" class="mb-1 text-sm font-bold uppercase tracking-wider text-emerald-800">Total a pagar al proveedor</h3>
                <div id="resultadoMonto" class="my-1 text-4xl font-black text-emerald-900">$0.00</div>
                <p id="resultadoMensaje" class="mt-1 text-xs font-bold uppercase tracking-wide text-emerald-700">Calculando…</p>
            </div>
        </div>

        <!-- Acciones -->
        <div class="mt-8 flex flex-wrap gap-3 border-t border-slate-200 pt-4">
            <a href="{{ route('proveedores.facturas.index', $liquidada ? ['estado' => 'liquidadas'] : []) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-arrow-left"></i> Volver a guías
            </a>
            @unless ($liquidada)
                <button type="submit" id="btnGenerarPdf" @disabled($factura->animales->isEmpty())
                        data-confirm="Se liquidará la guía {{ $factura->numeroFactura }}. Los anticipos seleccionados se descuentan y ya no volverán a salir; si sobra algo de un anticipo, queda para la próxima compra. ¿Confirmar?"
                        data-titulo="Confirmar pago" data-boton="Sí, confirmar pago"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Confirmar pago y generar PDF
                </button>
            @endunless
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const totalGanado = {{ $totalGanadoCalculado }};
        const liquidada = @js($liquidada);
        const fmt = (v) => '$' + Number(v).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const filas = document.querySelectorAll('.adelanto-row');
        const caja = document.getElementById('cajaResultado');
        const titulo = document.getElementById('resultadoTitulo');
        const monto = document.getElementById('resultadoMonto');
        const mensaje = document.getElementById('resultadoMensaje');
        const estilos = {
            pagar: ['border-emerald-500 bg-emerald-50', 'text-emerald-800', 'text-emerald-900', 'text-emerald-700'],
            debe:  ['border-rose-500 bg-rose-50', 'text-rose-800', 'text-rose-900', 'text-rose-700'],
            cero:  ['border-blue-500 bg-blue-50', 'text-blue-800', 'text-blue-900', 'text-blue-700'],
        };

        function pintar(tipo, t, m) {
            const [c, tt, mm, ms] = estilos[tipo];
            caja.className = 'flex flex-col justify-center rounded-xl border-2 p-6 text-center transition-all duration-300 ' + c;
            titulo.className = 'mb-1 text-sm font-bold uppercase tracking-wider ' + tt;
            monto.className = 'my-1 text-4xl font-black ' + mm;
            mensaje.className = 'mt-1 text-xs font-bold uppercase tracking-wide ' + ms;
            titulo.textContent = t; mensaje.textContent = m;
        }

        function recalcular() {
            let anticipos = 0;
            filas.forEach(f => {
                const cb = f.querySelector('.checkbox-adelanto');
                if (liquidada || (cb && cb.checked)) anticipos += parseFloat(f.dataset.monto) || 0;
            });
            // Solo se descuenta hasta cubrir el ganado; lo que sobre queda para la próxima compra
            const descontado = Math.min(anticipos, totalGanado);
            const sobrante = Math.round((anticipos - descontado) * 100) / 100;
            const diferencia = Math.round((totalGanado - descontado) * 100) / 100;
            document.getElementById('resumenTotalAdelantos').textContent = '−' + fmt(descontado);
            monto.textContent = fmt(diferencia);
            document.getElementById('resumenResultado').textContent = fmt(diferencia);
            const nota = document.getElementById('notaSobrante');
            nota.classList.toggle('hidden', sobrante <= 0);
            nota.innerHTML = '<i class="fa-solid fa-circle-info"></i> Sobran <strong>' + fmt(sobrante) + '</strong> de anticipo: no se pierden, quedan a cuenta del proveedor para su próxima compra.';

            if (diferencia > 0) pintar('pagar', liquidada ? 'Pagado al proveedor' : 'Total a pagar al proveedor', liquidada ? 'Liquidación cerrada' : 'Diferencia a pagar');
            else pintar('cero', 'Cubierto con anticipos', sobrante > 0 ? 'Sobran ' + fmt(sobrante) + ' para la próxima compra' : 'Los anticipos cubren exactamente la compra');
        }

        document.querySelectorAll('.checkbox-adelanto').forEach(cb => cb.addEventListener('change', recalcular));
        recalcular();
    });
</script>
</x-app-layout>
