<x-app-layout>
    <div class="py-12" x-data="loteCompraData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensajes de Error o Alerta general -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Atención!</strong>
                    <span class="block sm:inline">Por favor revisa los campos obligatorios o errores en la lista de animales.</span>
                </div>
            @endif

            <form action="#" method="POST">
                @csrf

                <!-- SECCIÓN 1: Datos Generales de la Compra -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Registro de Compra de Ganado por Lote
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Proveedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Proveedor *</label>
                            <select name="supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Seleccione un proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }} @if($proveedor->razon_social) ({{ $proveedor->razon_social }}) @endif</option>
                                @endforeach
                            </select>
                            @error('proveedor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha de Compra -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Compra *</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('purchase_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Número de Factura -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° de Factura / Recibo</label>
                            <input type="text" name="invoice_number" placeholder="Ej. FAC-00123"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: Captura Rápida de Animales (Escáner / Digitación) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">
                        Agregar Animales al Lote
                    </h3>

                    <!-- Mini formulario de captura por animal -->
                    <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg items-end border border-gray-200 dark:border-gray-700">
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">N° de Arete *</label>
                            <input type="text" x-model="form.tag_number" id="input_scan" placeholder="Escanee o digite" 
                                @keydown.enter.prevent="agregarAnimal()"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Raza</label>
                            <input type="text" x-model="form.breed" placeholder="Ej. Brahman, Angus" 
                                @keydown.enter.prevent="agregarAnimal()"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Sexo *</label>
                            <select x-model="form.gender" class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="Macho">Macho</option>
                                <option value="Hembra">Hembra</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Peso actual (kg) *</label>
                            <input type="number" step="0.01" x-model="form.current_weight" placeholder="0.00" 
                                @keydown.enter.prevent="agregarAnimal()"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <button type="button" @click="agregarAnimal()" 
                                class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-md shadow transition">
                                + Agregar a lista
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-indigo-500 mt-2">💡 Tip: El lector pistola de aretes mandará el código y al presionar Enter se agregará automáticamente sin usar el mouse.</p>

                    <!-- SECCIÓN 3: Tabla Resumen del Lote Temporal -->
                    <div class="mt-6 overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"># Arete</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Raza</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sexo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Peso (kg)</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(animal, index) in animales" :key="index">
                                    <tr>
                                        <!-- Inputs ocultos para enviarlos al controlador como un array PHP -->
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            <span x-text="animal.tag_number"></span>
                                            <input type="hidden" :name="`animals[${index}][tag_number]`" :value="animal.tag_number">
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span x-text="animal.breed || 'N/D'"></span>
                                            <input type="hidden" :name="`animals[${index}][breed]`" :value="animal.breed">
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span x-text="animal.gender"></span>
                                            <input type="hidden" :name="`animals[${index}][gender]`" :value="animal.gender">
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span x-text="animal.current_weight"></span> kg
                                            <input type="hidden" :name="`animals[${index}][current_weight]`" :value="animal.current_weight">
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            <button type="button" @click="quitarAnimal(index)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 font-medium">Quitar</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="animales.length === 0">
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Aún no hay animales agregados a este lote. Usa el panel superior para comenzar a escanear o escribir los aretes.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Contador total flotante -->
                    <div class="mt-4 flex justify-between items-center text-sm text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <span>Total de animales listos para guardar: <strong x-text="animales.length" class="text-indigo-600 dark:text-indigo-400 text-lg"></strong></span>
                    </div>
                </div>

                <!-- Botones de Acción Global -->
                <div class="flex justify-end space-x-3">
                    <a href="#" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                        Cancelar
                    </a>
                    <button type="submit" :disabled="animales.length === 0" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow transition">
                        Registrar Lote de Compra
                    </button>
                </div>

            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function loteCompraData() {
            return {
                animales: [],
                form: {
                    tag_number: '',
                    breed: '',
                    gender: 'Macho',
                    current_weight: ''
                },
                agregarAnimal() {
                    // Validar campos obligatorios del animal actual
                    if (!this.form.tag_number || !this.form.current_weight) {
                        alert('El Número de Arete y el Peso son obligatorios.');
                        return;
                    }
                    
                    // Validar que no esté duplicado en la lista actual del lote
                    if (this.animales.some(a => a.tag_number === this.form.tag_number)) {
                        alert('Este número de arete ya fue agregado a la lista.');
                        return;
                    }

                    // Agregar copia del objeto al array
                    this.animales.push({ ...this.form });

                    // Limpiar arete y peso para el siguiente escaneo rápido (mantiene raza/género por comodidad)
                    this.form.tag_number = '';
                    this.form.current_weight = '';

                    // Enfocar nuevamente el input de arete para pistola lectora
                    this.$nextTick(() => {
                        document.getElementById('input_scan').focus();
                    });
                },
                quitarAnimal(index) {
                    this.animales.splice(index, 1);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>