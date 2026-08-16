<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes de Error o Alerta general -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Atención!</strong>
                    <span class="block sm:inline">Por favor revisa los campos obligatorios o errores en la Creación de factura.</span>
                    <ul class="mt-2 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- SECCIÓN DEL FORMULARIO CON RUTA DE LARAVEL -->
            <form action="{{ route('proveedores.facturas.ganado.store') }}" method="POST" id="form-lote-compra">
                @csrf

                <!-- SECCIÓN 1: Datos Generales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Creación de Factura de Compra de Ganado
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Proveedor (Alineado con proveedorID) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor *</label>
                            <select name="proveedor_id" id="proveedor_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombreContacto }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fecha de Compra (Alineado con fechaFactura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Compra *</label>
                            <input type="date" name="fecha_factura" id="fecha_factura" value="{{ date('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <!-- Número de Factura (Alineado con numeroFactura) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° de Factura / Recibo *</label>
                            <input type="text" name="factura" id="factura" placeholder="Ej. FAC-00123" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <!-- Concepto / Notas (Alineado con notas) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Concepto / Notas</label>
                            <input type="text" name="observaciones" id="observaciones" placeholder="Comentarios adicionales"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <button type="submit" id="btn-create-factura"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow transition">
                        Crear Factura y Registrar Ganado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script de validación en el cliente antes de enviar -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("form-lote-compra");
            const proveedorId = document.getElementById("proveedor_id");
            const fechaFactura = document.getElementById("fecha_factura");
            const factura = document.getElementById("factura");
            const observaciones = document.getElementById("observaciones");
            const btnSubmit = document.getElementById("btn-create-factura");

            form.addEventListener("submit", function(e) {
                // Validaciones básicas de JavaScript por seguridad
                if (!proveedorId.value) {
                    e.preventDefault();
                    alert("Por favor selecciona un proveedor.");
                    proveedorId.focus();
                    return;
                }

                if (!factura.value.trim()) {
                    e.preventDefault();
                    alert("Por favor ingresa el número de factura.");
                    factura.focus();
                    return;
                }

                if (!fechaFactura.value) {
                    e.preventDefault();
                    alert("Por favor selecciona una fecha de factura.");
                    fechaFactura.focus();
                    return;
                }

                // Si todo está correcto, el formulario se envía de manera nativa a Laravel
     // alert("Formulario enviado correctamente. Se creará la factura y se registrará el ganado.");
            });
        });
    </script>
</x-app-layout>
