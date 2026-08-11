<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes de Error o Alerta general -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Atención!</strong>
                    <span class="block sm:inline">Por favor revisa los campos obligatorios del formulario.</span>
                </div>
            @endif

            <form action="{{ route('adelantos.proveedores.store', $proveedor->id) }}" method="POST"
                id="form-adelanto-proveedor">
                @csrf

                <!-- SECCIÓN 1: Datos Generales del Adelanto -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        Registro de Adelanto o Préstamo a Proveedor
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Proveedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor
                                *</label>
                            <input type="text" value="{{ $proveedor->nombreContacto }}" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-sm cursor-not-allowed">
                            <input type="hidden" name="supplier_id" value="{{ $proveedor->id }}">
                        </div>
                           <!-- Fecha de Adelanto -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Adelanto
                            *</label>
                        <input type="date" name="advance_date" value="{{ date('Y-m-d') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    </div>




                </div>

                <!-- SECCIÓN 2: Detalle del Concepto y Monto -->
                <div
                    class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4">
                    <h3 class="text-md font-medium text-gray-800 dark:text-gray-200">Detalle del Concepto y Equivalencia
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Concepto -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Concepto /
                                Descripción *</label>
                            <input type="text" name="concept" id="input_concept"
                                placeholder="Ej. Préstamo personal o 2 sacos de concentrado" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            <span class="text-xs text-gray-500 mt-1 block">Detalle qué se le entrega al proveedor
                                (dinero o productos).</span>
                        </div>

                        <!-- Monto Equivalente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Monto Equivalente
                                ($) *</label>
                            <div class="mt-1 relative rounded-md shadow-sm">

                                <input type="number" step="0.01" name="amount" id="input_amount" placeholder="0.00"
                                    required
                                    class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <span class="text-xs text-gray-500 mt-1 block">Valor monetario total del adelanto.</span>
                        </div>
                    </div>


                </div>
        </div>

        <!-- Resumen Rápido -->
        <div
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6 flex justify-between items-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">Total a registrar como adelanto al proveedor:</span>
            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">$<span
                    id="display-total">0.00</span></span>
        </div>

        <!-- Botones de Acción Global -->
        <div class="flex justify-end space-x-3">
            <a href="#"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                Cancelar
            </a>
            <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow transition">
                Guardar Adelanto
            </button>
        </div>

        </form>
    </div>
    </div>

    <!-- Script sencillo para reflejar el total en tiempo real -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputAmount = document.getElementById("input_amount");
            const displayTotal = document.getElementById("display-total");

            inputAmount.addEventListener("input", function() {
                let val = parseFloat(inputAmount.value) || 0;
                displayTotal.textContent = val.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            });
        });
    </script>
</x-app-layout>
