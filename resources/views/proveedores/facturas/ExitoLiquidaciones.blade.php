<x-app-layout>
    <div class="container mx-auto p-6 max-w-2xl my-12">

        <!-- Tarjeta de Éxito Estilizada -->
        <div class="bg-white shadow-xl rounded-3xl border border-slate-100 p-8 sm:p-12 text-center space-y-6 relative overflow-hidden">

            <!-- Detalle decorativo superior -->
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600"></div>

            <!-- Icono de Éxito Animado / Sólido -->
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center mx-auto text-3xl shadow-inner border border-emerald-100">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <!-- Títulos y Mensajes -->
            <div class="space-y-2">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider rounded-full">
                    Transacción Exitosa
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    ¡Liquidación Completada con Éxito!
                </h1>
                <p class="text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                    La liquidación correspondiente al proveedor <strong class="text-slate-700">{{ $factura->proveedor->nombreContacto ?? 'N/D' }}</strong> ha sido procesada y registrada en el sistema correctamente.
                </p>
            </div>

            <!-- Resumen Rápido del Comprobante -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 max-w-md mx-auto grid grid-cols-2 gap-4 text-left text-xs">
                <div>
                    <span class="text-slate-400 block uppercase font-semibold">Guia de Liquidacion N°</span>
                    <strong class="text-slate-800 text-sm">{{ $factura->numeroFactura ?? 'S/N' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block uppercase font-semibold">Fecha de Liquidación</span>
                    <strong class="text-slate-800 text-sm">{{ date('d/m/Y') }}</strong>
                </div>
            </div>

            <!-- Botones de Acción Elegantes -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4 border-t border-slate-100">

                <!-- Botón para Generar / Descargar PDF -->
                <a href="{{ route('proveedores.facturas.liquidar.generarPdf', $factura->id) }}" target="_blank" class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition shadow-md flex items-center justify-center gap-2 text-sm group">
                    <i class="fa-solid fa-file-pdf text-base group-hover:scale-110 transition-transform"></i> Generar Comprobante PDF
                </a>

                <!-- Botón para Volver al Listado de Liquidaciones -->
                <a href="{{ route('proveedores.facturas.index') }}" class="w-full sm:w-auto px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition flex items-center justify-center gap-2 text-sm">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Volver al Listado
                </a>

            </div>

        </div>

    </div>
</x-app-layout>
