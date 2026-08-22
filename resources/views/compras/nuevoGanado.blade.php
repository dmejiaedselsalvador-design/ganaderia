<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes de Error o Alerta general -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Atención!</strong>
                    <span class="block sm:inline">Por favor revisa los campos obligatorios o errores en la lista de animales.</span>
                </div>
            @endif

            <form action="{{ route('compras.ganado.store') }}" method="POST" id="form-lote-compra">
                @csrf

                <!-- SECCIÓN 1: Datos Generales y Configuración de Precios -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Registro de Compra de Ganado por Lote (Escalas de Precio)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Proveedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente Proveedor</label>
                            <div class="mt-1 text-gray-900 dark:text-white font-semibold">
                                {{ $proveedorSeleccionado->nombreContacto ?? 'No seleccionado' }}
                            </div>
                        </div>

                        <!-- Factura -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Factura de Compra</label>
                            <div class="mt-1 text-gray-900 dark:text-white font-semibold">
                                {{ $factura->numeroFactura ?? 'No factura' }}
                            </div>
                            <input name="factura-id" id="factura-id" value="{{ $factura->id ?? '' }}" type="hidden">
                        </div>

                        <!-- Fecha de Compra -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Compra *</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Configuración de Precios Base por Escala (Estilo Excel) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 font-semibold">Precio Macho Base:</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="number" step="0.01" id="precio_macho_base" value="100"
                                    class="w-24 rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:text-white">
                                <span class="text-xs text-gray-500">Hasta los</span>
                                <input type="number" id="peso_limite_macho" value="150"
                                    class="w-20 rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:text-white">
                                <span class="text-xs text-gray-500">kg</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 font-semibold">Precio Hembra Base:</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="number" step="0.01" id="precio_hembra_base" value="100"
                                    class="w-24 rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:text-white">
                                <span class="text-xs text-gray-500">Hasta los</span>
                                <input type="number" id="peso_limite_hembra" value="150"
                                    class="w-20 rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:text-white">
                                <span class="text-xs text-gray-500">kg</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 font-semibold">Factor Castigo:</label>
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="number" step="0.01" id="factor-castigo" value="0.02"
                                    class="w-24 rounded-md border-gray-300 text-sm dark:bg-gray-700 dark:text-white">
                                <span class="text-xs text-gray-500">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: Captura Rápida de Animales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3" id="form-title-accion">
                        Agregar Animales al Lote
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg items-end border border-gray-200 dark:border-gray-700">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">N° de Arete *</label>
                            <input type="text" id="input_tag_number" placeholder="Escanee o digite"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Raza</label>
                            <input type="text" id="input_breed" placeholder="Ej. Brahman"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Categoría / Tipo *</label>
                            <select id="input_gender" name="categoria"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="" disabled selected>Seleccione categoría</option>
                                <optgroup label="Machos">
                                    <option value="Becerro">Becerro</option>
                                    <option value="Torete">Torete</option>
                                    <option value="Toro">Toro</option>
                                </optgroup>
                                <optgroup label="Hembras">
                                    <option value="Becerra">Becerra</option>
                                    <option value="Vaquilla">Vaquilla</option>
                                    <option value="Vaca">Vaca</option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Peso actual (kg) *</label>
                            <input type="number" step="0.01" id="input_weight" placeholder="0.00"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Observación</label>
                            <input type="text" id="input_observation" placeholder="Ej. Flaco, oreja rajada"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <button id="btn-agregar-ganado" type="button"
                                class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-md shadow transition">
                                + Agregar
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-indigo-500 mt-2">💡 Tip: Puede usar el lector de aretes y presionar Enter para agilizar la captura.</p>

                    <!-- CONTENEDOR DINÁMICO DE TABLAS POR CATEGORÍA -->
                    <div id="contenedor-dinamico-tablas" class="mt-8 space-y-8">
                        <!-- Las tablas se renderizarán automáticamente aquí solo si tienen elementos -->
                    </div>

                    <!-- Mensaje si no hay animales -->
                    <div id="sin-animales-msg" class="mt-8 text-center py-8 text-gray-500 dark:text-gray-400 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
                        No hay animales agregados al lote todavía.
                    </div>

                    <!-- Contador General Global -->
                    <div class="mt-6 flex justify-between items-center text-sm text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <span>Total general de animales listos: <strong id="contador-animales" class="text-indigo-600 dark:text-indigo-400 text-lg">0</strong></span>
                        <span class="text-lg font-bold text-gray-800 dark:text-white">Gran Total Lote: $<span id="gran-total-lote">0.00</span></span>
                    </div>
                </div>

                <!-- Inputs ocultos inyectados para Laravel -->
                <div id="inputs-ocultos-container"></div>

                <!-- Botones de Acción Global -->
                <div class="flex justify-end space-x-3">
                    <a href="#" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
                        Cancelar
                    </a>
                    <button type="submit" id="btn-submit-lote" disabled
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed shadow transition">
                        Registrar Lote de Compra
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Script JavaScript Vanilla para la gestión dinámica -->
   <script>
    document.addEventListener("DOMContentLoaded", function() {
    let animales = [];
    let editandoIndex = null;

    const inputTag = document.getElementById("input_tag_number");
    const inputBreed = document.getElementById("input_breed");
    const inputGender = document.getElementById("input_gender");
    const inputWeight = document.getElementById("input_weight");
    const inputObservation = document.getElementById("input_observation");

    const precioMachoBaseInput = document.getElementById("precio_macho_base");
    const pesoLimiteMachoInput = document.getElementById("peso_limite_macho");
    const precioHembraBaseInput = document.getElementById("precio_hembra_base");
    const pesoLimiteHembraInput = document.getElementById("peso_limite_hembra");
    const factorCastigoInput = document.getElementById("factor-castigo");

    const btnAgregar = document.getElementById("btn-agregar-ganado");
    const contenedorDinamico = document.getElementById("contenedor-dinamico-tablas");
    const sinAnimalesMsg = document.getElementById("sin-animales-msg");
    const contadorAnimales = document.getElementById("contador-animales");
    const granTotalLote = document.getElementById("gran-total-lote");
    const btnSubmit = document.getElementById("btn-submit-lote");
    const inputsContainer = document.getElementById("inputs-ocultos-container");
    const formTitleAccion = document.getElementById("form-title-accion");

    // Cálculo de precio por escala
    function calcularPrecioUnitario(gender, weight) {
        let w = parseFloat(weight);
        let factorCastigo = parseFloat(factorCastigoInput.value) || 0.02;

        if (gender === 'Macho') {
            let base = parseFloat(precioMachoBaseInput.value) || 100;
            let limite = parseFloat(pesoLimiteMachoInput.value) || 150;

            if (w <= limite) {
                return base;
            } else {
                let kilosExcedidos = w - limite;
                let rebaja = kilosExcedidos * (base * (factorCastigo / 10));
                return Math.max(0, base - rebaja);
            }
        } else {
            let base = parseFloat(precioHembraBaseInput.value) || 100;
            let limite = parseFloat(pesoLimiteHembraInput.value) || 150;

            if (w <= limite) {
                return base;
            } else {
                let kilosExcedidos = w - limite;
                let rebaja = kilosExcedidos * (base * (factorCastigo / 10));
                return Math.max(0, base - rebaja);
            }
        }
    }

    function guardarOActualizarAnimal() {
        const tag = inputTag.value.trim();
        const breed = inputBreed.value.trim() || 'Sin especificar';
        const categoria = inputGender.value;
        const weight = inputWeight.value.trim();
        const observation = inputObservation.value.trim() || '';

        if (!tag) {
            alert("Por favor ingresa o escanea el número de arete.");
            inputTag.focus();
            return;
        }

        if (!categoria) {
            alert("Por favor selecciona una categoría.");
            inputGender.focus();
            return;
        }

        if (!weight || parseFloat(weight) <= 0) {
            alert("Por favor ingresa un peso válido.");
            inputWeight.focus();
            return;
        }

        if (animales.some((a, idx) => a.tag === tag && idx !== editandoIndex)) {
            alert("Este número de arete ya se encuentra registrado en el lote.");
            inputTag.focus();
            return;
        }

        // Determinar el sexo de forma automática según la categoría elegida
        const esMacho = ['Becerro', 'Torete', 'Toro'].includes(categoria);
        const gender = esMacho ? 'Macho' : 'Hembra';

        const unitPrice = calcularPrecioUnitario(gender, weight);
        const total = parseFloat(weight) * unitPrice;

        const animalData = {
            tag,
            breed,
            categoria,
            gender,
            weight,
            unitPrice,
            total,
            observation
        };

        if (editandoIndex !== null) {
            animales[editandoIndex] = animalData;
            editandoIndex = null;
            btnAgregar.textContent = "+ Agregar";
            btnAgregar.classList.remove("bg-amber-600", "hover:bg-amber-700");
            btnAgregar.classList.add("bg-emerald-600", "hover:bg-emerald-700");
            formTitleAccion.textContent = "Agregar Animales al Lote";
        } else {
            animales.push(animalData);
        }

        inputTag.value = "";
        inputBreed.value = "";
        inputGender.value = "";
        inputWeight.value = "";
        inputObservation.value = "";
        inputTag.focus();

        renderizarTablas();
    }

    btnAgregar.addEventListener("click", guardarOActualizarAnimal);

    // Evento Enter en los inputs para agilizar el guardado con pistola de aretes
    [inputTag, inputBreed, inputGender, inputWeight, inputObservation].forEach(element => {
        element.addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                guardarOActualizarAnimal();
            }
        });
    });

    window.editarAnimalPorId = function(index) {
        const animal = animales[index];
        inputTag.value = animal.tag;
        inputBreed.value = animal.breed === 'Sin especificar' ? '' : animal.breed;
        inputGender.value = animal.categoria;
        inputWeight.value = animal.weight;
        inputObservation.value = animal.observation;

        editandoIndex = index;
        btnAgregar.textContent = "Actualizar";
        btnAgregar.classList.remove("bg-emerald-600", "hover:bg-emerald-700");
        btnAgregar.classList.add("bg-amber-600", "hover:bg-amber-700");
        formTitleAccion.textContent = "Editando Animal (Arete: " + animal.tag + ")";
        inputTag.focus();
    }

    window.eliminarAnimalPorId = function(index) {
        if (editandoIndex === index) {
            editandoIndex = null;
            inputTag.value = "";
            inputBreed.value = "";
            inputGender.value = "";
            inputWeight.value = "";
            inputObservation.value = "";
            btnAgregar.textContent = "+ Agregar";
            btnAgregar.classList.remove("bg-amber-600", "hover:bg-amber-700");
            btnAgregar.classList.add("bg-emerald-600", "hover:bg-emerald-700");
            formTitleAccion.textContent = "Agregar Animales al Lote";
        } else if (editandoIndex > index) {
            editandoIndex--;
        }

        animales.splice(index, 1);
        renderizarTablas();
    }

    function renderizarTablas() {
        contenedorDinamico.innerHTML = "";
        inputsContainer.innerHTML = "";

        if (animales.length === 0) {
            sinAnimalesMsg.style.display = "block";
            contadorAnimales.textContent = "0";
            granTotalLote.textContent = "0.00";
            btnSubmit.disabled = true;
            return;
        }

        sinAnimalesMsg.style.display = "none";
        btnSubmit.disabled = false;

        const categorias = ['Becerro', 'Torete', 'Toro', 'Becerra', 'Vaquilla', 'Vaca'];
        let granTotalSuma = 0;
        let totalAnimalesGlobal = 0;

        categorias.forEach(cat => {
            let animalesCat = animales.filter(a => a.categoria === cat);

            if (animalesCat.length === 0) return; // Si no hay de esta categoría, se omite

            let sumaPeso = 0;
            let sumaMonto = 0;
            let indexCorrelativo = 1; // Comienza en 1 para cada tabla de categoría
            let filasHTML = "";

            animalesCat.forEach((a) => {
                let realIndex = animales.indexOf(a);

                a.unitPrice = calcularPrecioUnitario(a.gender, a.weight);
                a.total = parseFloat(a.weight) * a.unitPrice;

                sumaPeso += parseFloat(a.weight);
                sumaMonto += a.total;

                granTotalSuma += a.total;
                totalAnimalesGlobal++;

                filasHTML += `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-center text-sm font-bold text-indigo-600 dark:text-indigo-400">${indexCorrelativo++}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">${a.tag}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">${a.breed}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">${a.weight} kg</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">$${a.unitPrice.toFixed(2)}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">$${a.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 italic">${a.observation || 'Ninguna'}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-2">
                            <button type="button" onclick="editarAnimalPorId(${realIndex})" class="text-amber-600 hover:text-amber-900 font-medium">Editar</button>
                            <button type="button" onclick="eliminarAnimalPorId(${realIndex})" class="text-red-600 hover:text-red-900 font-medium">Eliminar</button>
                        </td>
                    </tr>
                `;

                // Inyectar inputs ocultos para Laravel
          // Inyectar inputs ocultos para Laravel con los nombres exactos que espera tu validación
inputsContainer.innerHTML += `
    <input type="hidden" name="animals[${realIndex}][tag_number]" value="${a.tag}">
    <input type="hidden" name="animals[${realIndex}][breed]" value="${a.breed}">
    <input type="hidden" name="animals[${realIndex}][category]" value="${cat}">
    <input type="hidden" name="animals[${realIndex}][gender]" value="${a.gender}">
    <input type="hidden" name="animals[${realIndex}][weight]" value="${a.weight}">
    <input type="hidden" name="animals[${realIndex}][unit_price]" value="${a.unitPrice.toFixed(2)}">
    <input type="hidden" name="animals[${realIndex}][total]" value="${a.total.toFixed(2)}">
    <input type="hidden" name="animals[${realIndex}][observation]" value="${a.observation}">
`;
            });

            let promedioPeso = (sumaPeso / animalesCat.length).toFixed(2);
            let emojiIcono = ['Becerro', 'Torete', 'Toro'].includes(cat) ? '🐂' : '🐄';

            // Generar la tabla con el HTML limpio y espaciado correcto en el thead
            contenedorDinamico.innerHTML += `
                <div>
                    <h4 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-2">${emojiIcono} Tabla de ${cat}s</h4>
                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase w-12">N°</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Arete</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Raza</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peso (kg)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total ($)</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Observación</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                ${filasHTML}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-300 font-semibold flex justify-between bg-gray-50 dark:bg-gray-900 p-3 rounded">
                        <span>Promedio de Peso: <span class="text-indigo-600">${promedioPeso}</span> kg</span>
                        <span>Total ${cat}s: $<span class="text-gray-900 dark:text-white">${sumaMonto.toFixed(2)}</span></span>
                    </div>
                </div>
            `;
        });

        contadorAnimales.textContent = totalAnimalesGlobal;
        granTotalLote.textContent = granTotalSuma.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
});
   </script>
</x-app-layout>
