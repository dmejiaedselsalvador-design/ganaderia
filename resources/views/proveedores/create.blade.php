<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes de Error o Alerta general -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Atención!</strong>
                    <span class="block sm:inline">Por favor revisa los campos obligatorios del formulario.</span>
                    <ul class="mt-2 list-disc list-inside text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('proveedor.nuevo')  }}" method="POST" id="form-create-proveedor">
                @csrf

                <!-- SECCIÓN 1: Datos Generales del Proveedor -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Creación de Proveedor
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Nombre del Proveedor -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Proveedor / Rancho *</label>
                            <input type="text" name="nombreProoveedor" id="nombreProoveedor" value="{{ old('nombreProoveedor') }}" placeholder="Ej. Rancho San José o Agroservicios" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        </div>



                        <!-- Nombre de Contacto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Persona de Contacto</label>
                            <input type="text" name="nombreContacto" id="nombreContacto" value="{{ old('nombreContacto') }}" placeholder="Ej. Don Carlos Pérez"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" placeholder="Ej. 7000-0000"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        </div>

                        <!-- Lugar (Ciudad o Región) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ciudad / Región (Lugar)</label>
                            <input type="text" name="lugar" id="lugar" value="{{ old('lugar') }}" placeholder="Ej. Santa Ana"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        </div>

                        <!-- Razón Social -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Razón Social</label>
                            <input type="text" name="razon_social" id="razon_social" value="{{ old('razon_social') }}" placeholder="Nombre fiscal o empresa"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">
                        </div>

                        <!-- Ubicación Detallada -->
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación / Dirección Exacta</label>
                            <textarea name="ubicacion" rows="2" placeholder="Dirección física o referencias del rancho..."
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white text-sm">{{ old('ubicacion') }}</textarea>
                        </div>
                    </div>
                </div>



                <!-- Botones de Acción Global -->
                <div class="flex justify-end space-x-3">

                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow transition">
                        Guardar Proveedor
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Script para el total y mantener valores antiguos si hay errores de validación -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputAmount = document.getElementById("input_amount");
            const displayTotal = document.getElementById("display-total");

            function updateTotal() {
                let val = parseFloat(inputAmount.value) || 0;
                displayTotal.textContent = val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            inputAmount.addEventListener("input", updateTotal);

            // Ejecutar al cargar por si recarga con old() values
            updateTotal();
        });
    </script>
</x-app-layout>
