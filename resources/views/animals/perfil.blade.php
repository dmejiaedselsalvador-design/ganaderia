<x-app-layout>
    <style>
    [x-cloak] { display: none !important; }
</style>
    <!-- Contenedor principal con estado Alpine para controlar los modales -->
    <div class="py-4 sm:py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ modalPesajeOpen: false, modalVacunaOpen: false }">

        <!-- 1. BARRA DE BÚSQUEDA Y ACCESO RÁPIDO -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-4 sm:p-5 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg shrink-0">
                    <i class="fa-solid fa-cow"></i>
                </div>
                <div>
                    <h2 class="font-bold text-slate-800 text-base sm:text-lg">Expediente Operativo</h2>
                    <p class="text-xs text-slate-500">Gestión de báscula, sanidad y alimentación en corral.</p>
                </div>
            </div>

            <!-- Buscador optimizado táctil -->
            <form action="{{ route('compras.ganado.perfil') }}" method="GET" class="flex items-center gap-2 w-full md:w-96">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="arete" value="{{ request('arete') }}" placeholder="Escanear o digitar Arete..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition shrink-0 shadow-xs">
                    Buscar
                </button>
            </form>
        </div>

        @if(isset($ganadoSeleccionado))
            <!-- 2. TARJETA DE RESUMEN DE IDENTIDAD -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 sm:p-6">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">

                    <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                        <div class="px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                            <span class="text-[10px] uppercase font-bold text-emerald-600 block tracking-wider">Arete ID</span>
                            <span class="text-xl font-black text-emerald-900">{{ $ganadoSeleccionado->areteID }}</span>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 text-base">
                                    {{ $ganadoSeleccionado->raza ?? 'Cruzado / General' }}
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $ganadoSeleccionado->status == 'activo' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $ganadoSeleccionado->status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 flex flex-wrap gap-x-4 gap-y-1">
                                <span>Sexo: <strong class="text-slate-700">{{ $ganadoSeleccionado->sexo }}</strong></span>
                                <span>Categoría: <strong class="text-slate-700">{{ $ganadoSeleccionado->categoria }}</strong></span>
                                <span>Proveedor: <strong class="text-slate-700">{{ $ganadoSeleccionado->proveedor->nombreContacto ?? 'N/D' }}</strong></span>
                            </p>
                        </div>
                    </div>

                    <!-- Botones de Acción Táctil (Disparan los modales Alpine) -->
                    <div class="flex items-center gap-2.5 w-full lg:w-auto">
                        <button @click="modalPesajeOpen = true" class="flex-1 lg:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-weight-scale"></i> + Pesaje
                        </button>
                        <button @click="modalVacunaOpen = true" class="flex-1 lg:flex-none bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center justify-center gap-2 shadow-xs">
                            <i class="fa-solid fa-syringe"></i> + Vacuna / Tratamiento
                        </button>
                    </div>

                </div>
            </div>

            <!-- 3. GRID DE TRABAJO RÁPIDO -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Métricas -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 space-y-4">
                        <h4 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                            <i class="fa-solid fa-gauge-high text-emerald-600"></i> Métricas del Hato
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                <span class="text-[11px] font-semibold text-slate-400 block uppercase">Último Peso</span>
                                <span class="text-lg font-black text-slate-800 mt-1 block">{{ $ganadoSeleccionado->ultimoPeso ?? 0 }} <span class="text-xs font-normal text-slate-500">kg</span></span>
                            </div>
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                <span class="text-[11px] font-semibold text-slate-400 block uppercase">Ingreso</span>
                                <span class="text-xs font-bold text-slate-700 mt-2 block">{{ $ganadoSeleccionado->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Tablas -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-5 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <i class="fa-solid fa-weight-scale text-emerald-600"></i> Bitácora de Pesajes
                            </h4>
                            <span class="text-xs text-slate-400">Historial de báscula</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider">
                                        <th class="p-3 rounded-l-lg">Fecha</th>
                                        <th class="p-3">Peso</th>
                                        <th class="p-3">Ganancia</th>
                                        <th class="p-3 rounded-r-lg">Notas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 text-slate-600 font-medium">01 Ago, 2026</td>
                                        <td class="p-3 font-bold text-emerald-700">480.00 kg</td>
                                        <td class="p-3 text-emerald-600 font-semibold">+15.00 kg</td>
                                        <td class="p-3 text-slate-500 text-xs">Corral de engorda principal.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- ========================================== -->
            <!-- MODAL 1: REGISTRAR PESAJE                     -->
            <!-- ========================================== -->
            <div x-show="modalPesajeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
                <div @click.outside="modalPesajeOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-100">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-weight-scale text-emerald-600"></i> Registrar Nuevo Pesaje
                        </h3>
                        <button @click="modalPesajeOpen = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Formulario de Pesaje -->
                    <form id="formPesaje" @submit.prevent="guardarPesaje()" class="space-y-4">
                        @csrf
                        <input type="hidden" name="ganado_id" value="{{ $ganadoSeleccionado->id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Arete Seleccionado</label>
                            <input type="text" disabled value="{{ $ganadoSeleccionado->areteID }}" class="w-full bg-slate-100 border border-slate-200 rounded-xl text-sm px-3 py-2 text-slate-600 font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Peso Actual (kg) *</label>
                            <input type="number" step="0.01" name="peso" required placeholder="Ej. 485.50" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Observaciones / Corral</label>
                            <textarea name="observaciones" rows="2" placeholder="Ej. Buen desarrollo en báscula..." class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="modalPesajeOpen = false" class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-xs">Guardar Pesaje</button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- ========================================== -->
            <!-- MODAL 2: APLICAR VACUNA / TRATAMIENTO       -->
            <!-- ========================================== -->
            <div x-show="modalVacunaOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
                <div @click.outside="modalVacunaOpen = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-100">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                            <i class="fa-solid fa-syringe text-indigo-600"></i> Registrar Vacuna / Tratamiento
                        </h3>
                        <button @click="modalVacunaOpen = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Formulario de Vacunación -->
                    <form id="formVacuna" @submit.prevent="guardarVacuna()" class="space-y-4">
                        @csrf
                        <input type="hidden" name="ganado_id" value="{{ $ganadoSeleccionado->id }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Tipo de Producto</label>
                            <select name="tipo" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                                <option value="vacuna">Vacuna</option>
                                <option value="medicamento">Medicamento / Desparasitante</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nombre del Producto / Lote *</label>
                            <input type="text" name="producto" required placeholder="Ej. Ivermectina / Lote #9921" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Dosis o Aplicación</label>
                            <input type="text" name="dosis" placeholder="Ej. 10 ml (Vía subcutánea)" class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="modalVacunaOpen = false" class="px-4 py-2 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-xs">Guardar Sanidad</button>
                        </div>
                    </form>
                </div>
            </div>

        @else
            <!-- Estado vacío minimalista -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-12 text-center space-y-3">
                <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Listo para escanear o buscar</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Digita el número de arete arriba para desplegar el expediente y registrar operaciones al instante.</p>
            </div>
        @endif

    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicialización de SweetAlert2 para confirmaciones
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition shadow-xs',
                    cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl text-sm font-semibold transition'
                },
                buttonsStyling: false
            });


        function guardarPesaje() {
            // Aquí puedes hacer tu petición AJAX (Axios / Fetch) hacia tu ruta de Laravel
            // Simulación visual con SweetAlert2 exitoso:
            Swal.fire({
                icon: 'success',
                title: '¡Pesaje registrado!',
                text: 'El nuevo peso se ha guardado correctamente en el expediente.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload(); // Recarga para actualizar datos
            });
        }

        function guardarVacuna() {
            Swal.fire({
                icon: 'success',
                title: '¡Sanidad registrada!',
                text: 'El registro de vacuna o medicamento se aplicó con éxito.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        }
          });
    </script>
</x-app-layout>
