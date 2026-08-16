<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Mensajes de Error o Alerta general -->
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">¡Atención!</strong>
                    <span class="block sm:inline">Por favor revisa los campos obligatorios o errores en la lista de
                        animales.</span>
                </div>
            @endif

            <form action="{{ route('compras.ganado.store') }}" method="POST" id="form-lote-compra">
                @csrf

                <!-- SECCIÓN 1: Datos Generales y Configuración de Precios -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Registro de Compra de Ganado por Lote (Escalas de Precio)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Proveedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente
                                Proveedor</label>

                            <!-- Texto visible para el usuario -->
                            <div class="mt-1 text-gray-900 dark:text-white font-semibold">
                                {{ $proveedorSeleccionado->nombreContacto ?? 'No seleccionado' }}
                            </div>
                        </div>

                          <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Factura de Compra</label>

                            <!-- Texto visible para el usuario -->
                            <div class="mt-1 text-gray-900 dark:text-white font-semibold">
                                {{ $factura->numeroFactura ?? 'No factura' }}
                            </div>

                            <input  name="factura-id" id="factura-id" value="{{ $factura->id }}" type="hidden">
                        </div>

                        <!-- Fecha de Compra -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Compra
                                *</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>


                    </div>

                    <!-- Configuración de Precios Base por Escala (Estilo Excel) -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div>
                            <label
                                class="block-xs font-medium text-gray-700 dark:text-gray-300 text-sm font-semibold">Precio
                                Macho Base:</label>
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
                            <label
                                class="block-xs font-medium text-gray-700 dark:text-gray-300 text-sm font-semibold">Precio
                                Hembra Base:</label>
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
                            <label
                                class="block-xs font-medium text-gray-700 dark:text-gray-300 text-sm font-semibold">Factor
                                Castigo:</label>
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

                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg items-end border border-gray-200 dark:border-gray-700">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">N° de Arete
                                *</label>
                            <input type="text" id="input_tag_number" placeholder="Escanee o digite"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Raza</label>
                            <input type="text" id="input_breed" placeholder="Ej. Brahman"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Sexo *</label>
                            <select id="input_gender"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="Macho">Macho</option>
                                <option value="Hembra">Hembra</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Peso actual (kg)
                                *</label>
                            <input type="number" step="0.01" id="input_weight" placeholder="0.00"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-500 dark:text-gray-400">Observación</label>
                            <input type="text" id="input_observation" placeholder="Ej. Flaco, pie quebrado"
                                class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <button id="btn-agregar-ganado" type="button"
                                class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-md shadow transition">
                                + Agregar
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-indigo-500 mt-2">💡 Tip: El lector pistola de aretes mandará el código y al
                        presionar Enter se agregará automáticamente sin usar el mouse.</p>

                    <!-- TABLA 1: MACHOS -->
                    <div class="mt-8">
                        <h4 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-2">🐂 Tabla de Machos</h4>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-12">
                                            N°</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            # Arete</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Raza</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Peso (kg)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Precio Unit.</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Total ($)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Observación</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-machos-body"
                                    class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">No hay machos
                                            agregados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="mt-2 text-sm text-gray-700 dark:text-gray-300 font-semibold flex justify-between bg-gray-50 dark:bg-gray-900 p-3 rounded">
                            <span>Promedio de Peso (Machos): <span id="promedio-peso-machos"
                                    class="text-indigo-600">0.00</span> kg</span>
                            <span>Total Machos: $<span id="total-monto-machos">0.00</span></span>
                        </div>
                    </div>

                    <!-- TABLA 2: HEMBRAS -->
                    <div class="mt-8">
                        <h4 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-2">🐄 Tabla de Hembras</h4>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-12">
                                            N°</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            # Arete</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Raza</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Peso (kg)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Precio Unit.</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Total ($)</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Observación</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-hembras-body"
                                    class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">No hay hembras
                                            agregadas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="mt-2 text-sm text-gray-700 dark:text-gray-300 font-semibold flex justify-between bg-gray-50 dark:bg-gray-900 p-3 rounded">
                            <span>Promedio de Peso (Hembras): <span id="promedio-peso-hembras"
                                    class="text-indigo-600">0.00</span> kg</span>
                            <span>Total Hembras: $<span id="total-monto-hembras">0.00</span></span>
                        </div>
                    </div>

                    <!-- Contador General Global -->
                    <div
                        class="mt-6 flex justify-between items-center text-sm text-gray-600 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <span>Total general de animales listos: <strong id="contador-animales"
                                class="text-indigo-600 dark:text-indigo-400 text-lg">0</strong></span>
                        <span class="text-lg font-bold text-gray-800 dark:text-white">Gran Total Lote: $<span
                                id="gran-total-lote">0.00</span></span>
                    </div>
                </div>

                <!-- Inputs ocultos inyectados para Laravel -->
                <div id="inputs-ocultos-container"></div>

                <!-- Botones de Acción Global -->
                <div class="flex justify-end space-x-3">
                    <a href="#"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition">
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

    <!-- Script de cálculo automático con correlativo y fórmula exacta de Excel -->
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

            const btnAgregar = document.getElementById("btn-agregar-ganado");
            const tablaMachosBody = document.getElementById("tabla-machos-body");
            const tablaHembrasBody = document.getElementById("tabla-hembras-body");

            const promedioPesoMachos = document.getElementById("promedio-peso-machos");
            const totalMontoMachos = document.getElementById("total-monto-machos");
            const promedioPesoHembras = document.getElementById("promedio-peso-hembras");
            const totalMontoHembras = document.getElementById("total-monto-hembras");

            const contadorAnimales = document.getElementById("contador-animales");
            const granTotalLote = document.getElementById("gran-total-lote");
            const btnSubmit = document.getElementById("btn-submit-lote");
            const inputsContainer = document.getElementById("inputs-ocultos-container");
            const formTitleAccion = document.getElementById("form-title-accion");
            const factorCastigoInput = document.getElementById("factor-castigo");

            // Fórmula exacta del Excel para el precio unitario
            // Fórmula exacta del Excel para el precio unitario
            function calcularPrecioUnitario(gender, weight) {
                let w = parseFloat(weight);
                let factorCastigo = parseFloat(factorCastigoInput.value) || 0.02;

                if (gender === 'Macho') {
                    let base = parseFloat(precioMachoBaseInput.value) || 80;
                    let limite = parseFloat(pesoLimiteMachoInput.value) || 150;

                    if (w <= limite) {
                        return base;
                    } else {
                        let kilosExcedidos = w - limite;
                        // Corrección: Aplicar la proporción de tu regla (cada 10 kg baja proporcional al factor)
                        let rebaja = kilosExcedidos * (base * (factorCastigo / 10));
                        return Math.max(0, base - rebaja);
                    }
                } else {
                    let base = parseFloat(precioHembraBaseInput.value) || 66;
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
                const gender = inputGender.value;
                const weight = inputWeight.value.trim();
                const observation = inputObservation.value.trim() || '';

                if (!tag) {
                    alert("Por favor ingresa o escanea el número de arete.");
                    inputTag.focus();
                    return;
                }

                if (!weight || parseFloat(weight) <= 0) {
                    alert("Por favor ingresa un peso válido.");
                    inputWeight.focus();
                    return;
                }

                if (animales.some((a, idx) => a.tag === tag && idx !== editandoIndex)) {
                    alert("Este número de arete ya se encuentra en la lista.");
                    inputTag.focus();
                    return;
                }

                const unitPrice = calcularPrecioUnitario(gender, weight);
                const total = parseFloat(weight) * unitPrice;

                if (editandoIndex !== null) {
                    animales[editandoIndex] = {
                        tag,
                        breed,
                        gender,
                        weight,
                        unitPrice,
                        total,
                        observation
                    };
                    editandoIndex = null;
                    btnAgregar.textContent = "+ Agregar";
                    btnAgregar.classList.remove("bg-amber-600", "hover:bg-amber-700");
                    btnAgregar.classList.add("bg-emerald-600", "hover:bg-emerald-700");
                    formTitleAccion.textContent = "Agregar Animales al Lote";
                } else {
                    animales.push({
                        tag,
                        breed,
                        gender,
                        weight,
                        unitPrice,
                        total,
                        observation
                    });
                }

                inputTag.value = "";
                inputBreed.value = "";
                inputWeight.value = "";
                inputObservation.value = "";
                inputTag.focus();

                renderizarTablas();
            }

            window.editarAnimalPorId = function(index) {
                const animal = animales[index];
                inputTag.value = animal.tag;
                inputBreed.value = animal.breed === 'Sin especificar' ? '' : animal.breed;
                inputGender.value = animal.gender;
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
                tablaMachosBody.innerHTML = "";
                tablaHembrasBody.innerHTML = "";
                inputsContainer.innerHTML = "";

                let machos = animales.filter(a => a.gender === 'Macho');
                let hembras = animales.filter(a => a.gender === 'Hembra');

                // Contador global que inicia en 1 y se mantiene para ambas tablas
                let correlativoGlobal = 1;

                // 1. Renderizar Machos
                if (machos.length === 0) {
                    tablaMachosBody.innerHTML =
                        `<tr><td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No hay machos agregados.</td></tr>`;
                    promedioPesoMachos.textContent = "0.00";
                    totalMontoMachos.textContent = "0.00";
                } else {
                    let sumaPesoM = 0;
                    let sumaMontoM = 0;

                    machos.forEach((m) => {
                        let realIndex = animales.indexOf(m);

                        m.unitPrice = calcularPrecioUnitario(m.gender, m.weight);
                        m.total = parseFloat(m.weight) * m.unitPrice;

                        sumaPesoM += parseFloat(m.weight);
                        sumaMontoM += m.total;

                        let numeroActual = correlativoGlobal++; // Asigna el número y luego aumenta en 1

                        tablaMachosBody.innerHTML += `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-center text-sm font-bold text-indigo-600 dark:text-indigo-400">${numeroActual}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">${m.tag}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">${m.breed}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">${m.weight} kg</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">$${m.unitPrice.toFixed(2)}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">$${m.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 italic">${m.observation || 'Ninguna'}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <button type="button" onclick="editarAnimalPorId(${realIndex})" class="text-amber-600 hover:text-amber-900 font-medium">Editar</button>
                                    <button type="button" onclick="eliminarAnimalPorId(${realIndex})" class="text-red-600 hover:text-red-900 font-medium">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                    promedioPesoMachos.textContent = (sumaPesoM / machos.length).toFixed(2);
                    totalMontoMachos.textContent = sumaMontoM.toFixed(2);
                }

                // 2. Renderizar Hembras (El contador sigue exactamente donde se quedó)
                if (hembras.length === 0) {
                    tablaHembrasBody.innerHTML =
                        `<tr><td colspan="8" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No hay hembras agregadas.</td></tr>`;
                    promedioPesoHembras.textContent = "0.00";
                    totalMontoHembras.textContent = "0.00";
                } else {
                    let sumaPesoH = 0;
                    let sumaMontoH = 0;

                    hembras.forEach((h) => {
                        let realIndex = animales.indexOf(h);

                        h.unitPrice = calcularPrecioUnitario(h.gender, h.weight);
                        h.total = parseFloat(h.weight) * h.unitPrice;

                        sumaPesoH += parseFloat(h.weight);
                        sumaMontoH += h.total;

                        let numeroActual = correlativoGlobal++; // Continúa la numeración desde los machos

                        tablaHembrasBody.innerHTML += `
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-center text-sm font-bold text-indigo-600 dark:text-indigo-400">${numeroActual}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">${h.tag}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">${h.breed}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">${h.weight} kg</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-300">$${h.unitPrice.toFixed(2)}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">$${h.total.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 italic">${h.observation || 'Ninguna'}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <button type="button" onclick="editarAnimalPorId(${realIndex})" class="text-amber-600 hover:text-amber-900 font-medium">Editar</button>
                                    <button type="button" onclick="eliminarAnimalPorId(${realIndex})" class="text-red-600 hover:text-red-900 font-medium">Eliminar</button>
                                </td>
                            </tr>
                        `;
                    });
                    promedioPesoHembras.textContent = (sumaPesoH / hembras.length).toFixed(2);
                    totalMontoHembras.textContent = sumaMontoH.toFixed(2);
                }

                contadorAnimales.textContent = animales.length;
                let granTotalNum = animales.reduce((acc, curr) => acc + curr.total, 0);
                granTotalLote.textContent = granTotalNum.toFixed(2);
                btnSubmit.disabled = animales.length === 0;

                // Inputs ocultos para Laravel
                animales.forEach((animal, index) => {
                    inputsContainer.innerHTML += `
                        <input type="hidden" name="animals[${index}][correlativo]" value="${index + 1}">
                        <input type="hidden" name="animals[${index}][tag_number]" value="${animal.tag}">
                        <input type="hidden" name="animals[${index}][breed]" value="${animal.breed}">
                        <input type="hidden" name="animals[${index}][gender]" value="${animal.gender}">
                        <input type="hidden" name="animals[${index}][weight]" value="${animal.weight}">
                        <input type="hidden" name="animals[${index}][unit_price]" value="${animal.unitPrice}">
                        <input type="hidden" name="animals[${index}][total]" value="${animal.total}">
                        <input type="hidden" name="animals[${index}][observation]" value="${animal.observation}">
                    `;
                });
            }

            // Eventos de escucha
            btnAgregar.addEventListener("click", (e) => {
                e.preventDefault();
                guardarOActualizarAnimal();
            });

            [inputTag, inputBreed, inputWeight, inputObservation].forEach(input => {
                input.addEventListener("keydown", (e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        guardarOActualizarAnimal();
                    }
                });
            });

            [precioMachoBaseInput, pesoLimiteMachoInput, precioHembraBaseInput, pesoLimiteHembraInput,
                factorCastigoInput
            ].forEach(input => {
                input.addEventListener("input", () => {
                    renderizarTablas();
                });
            });
        });
    </script>
</x-app-layout>
