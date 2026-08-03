<x-app-layout>
    <x-slot name="header">

            {{ __('Expediente del Animal (Perfil Clínico y Productivo)') }}

    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- ENCABEZADO ESTILO PERFIL (COVER & AVATAR) -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <!-- Portada del perfil -->
                <div class="h-40 bg-gradient-to-r from-emerald-700 via-teal-600 to-slate-800 relative p-6 flex justify-end items-start">
                    <span class="px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full bg-emerald-500/90 text-white shadow-sm backdrop-blur-xs">
                        Estatus: Activo
                    </span>
                </div>

                <!-- Contenido del perfil (Avatar superpuesto y datos principales) -->
                <div class="px-6 pb-6 pt-0 relative flex flex-col sm:flex-row items-center sm:items-end justify-between gap-4 -mt-16 sm:-mt-12">
                    <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4 text-center sm:text-left">
                        <!-- Icono / Avatar del animal -->
                        <div class="w-28 h-28 rounded-2xl bg-white border-4 border-white shadow-md flex items-center justify-center text-emerald-700 text-4xl bg-slate-50">
                            <i class="fa-solid fa-cow"></i>
                        </div>
                        <div class="mb-1">
                            <h3 class="text-2xl font-bold text-slate-900 flex items-center justify-center sm:justify-start gap-2">
                                MEX-849201
                                <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-normal">Arete SINIIGA</span>
                            </h3>
                            <p class="text-sm text-slate-500">Raza: <strong class="text-slate-700">Angus</strong> | Sexo: <strong class="text-slate-700">Macho</strong> | Procedencia: <span class="text-emerald-700 font-medium">Rancho El Sausalito</span></p>
                        </div>
                    </div>

                    <!-- Botones de acción rápida -->
                    <div class="flex items-center gap-2">
                        <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-weight-scale"></i> Registrar Pesaje
                        </button>
                        <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                            <i class="fa-solid fa-syringe"></i> Aplicar Vacuna
                        </button>
                    </div>
                </div>

                <!-- Pestañas de navegación del perfil (Estilo FB) -->
                <div class="px-6 border-t border-slate-100 flex gap-8 text-sm font-semibold text-slate-600">
                    <a href="#" class="py-4 border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2">
                        <i class="fa-solid fa-timeline"></i> Línea de Tiempo y Pesos
                    </a>
                    <a href="#" class="py-4 border-b-2 border-transparent hover:text-slate-900 transition flex items-center gap-2">
                        <i class="fa-solid fa-notes-medical"></i> Sanidad y Medicamentos
                    </a>
                    <a href="#" class="py-4 border-b-2 border-transparent hover:text-slate-900 transition flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Costos y Compra ({{ formatoPesos(24500) }})
                    </a>
                </div>
            </div>

            <!-- GRID DE CONTENIDO PRINCIPAL DEL PERFIL -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- COLUMNA IZQUIERDA: Resumen y Métricas Clave -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 space-y-4">
                        <h4 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-emerald-600"></i> Resumen Productivo
                        </h4>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Peso Inicial (Ingreso):</span>
                                <span class="font-semibold text-slate-800">420.00 kg</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Peso Actual en Báscula:</span>
                                <span class="font-bold text-emerald-700 text-base">480.00 kg</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Ganancia Total:</span>
                                <span class="font-semibold text-emerald-600">+60.00 kg</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Fecha de Adquisición:</span>
                                <span class="font-medium text-slate-700">12 de Mayo, 2026</span>
                            </div>
                        </div>
                    </div>

                    <!-- Alertas o Notas Veterinarias -->
                    <div class="bg-amber-50 rounded-xl border border-amber-200 p-6 space-y-2">
                        <h4 class="font-bold text-amber-800 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i> Próximo Refuerzo Sanitario
                        </h4>
                        <p class="text-xs text-amber-700 leading-relaxed">
                            Se programó la aplicación de desparasitante y refuerzo contra derriengue para el próximo <strong>15 de Agosto, 2026</strong>.
                        </p>
                    </div>
                </div>

                <!-- COLUMNA DERECHA / PRINCIPAL: Historial de Pesajes y Sanidad (Feed tipo muro) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Tarjeta: Historial de Pesos Históricos -->
                    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                                <i class="fa-solid fa-weight-scale text-emerald-600"></i> Historial de Báscula (Pesajes)
                            </h4>
                            <span class="text-xs text-slate-400">Actualizado al último pesaje</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                                        <th class="p-3">Fecha</th>
                                        <th class="p-3">Peso Registrado</th>
                                        <th class="p-3">Ganancia vs Anterior</th>
                                        <th class="p-3">Observaciones del Pesaje</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 text-slate-600 font-medium">01 Ago, 2026</td>
                                        <td class="p-3 font-bold text-emerald-700">480.00 kg</td>
                                        <td class="p-3 text-emerald-600 font-semibold">+15.00 kg</td>
                                        <td class="p-3 text-slate-500 text-xs">Buen desarrollo en corral de engorda.</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 text-slate-600 font-medium">01 Jul, 2026</td>
                                        <td class="p-3 font-bold text-slate-800">465.00 kg</td>
                                        <td class="p-3 text-emerald-600 font-semibold">+25.00 kg</td>
                                        <td class="p-3 text-slate-500 text-xs">Cambio a dieta de finalización.</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 text-slate-600 font-medium">12 Jun, 2026</td>
                                        <td class="p-3 font-bold text-slate-800">440.00 kg</td>
                                        <td class="p-3 text-slate-500 font-semibold">+20.00 kg</td>
                                        <td class="p-3 text-slate-500 text-xs">Pesaje de adaptación post-ingreso.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tarjeta: Control de Vacunaciones y Medicamentos -->
                    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6 space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                            <h4 class="font-bold text-slate-800 text-base flex items-center gap-2">
                                <i class="fa-solid fa-syringe text-emerald-600"></i> Historial de Vacunación y Medicamentos
                            </h4>
                        </div>

                        <div class="space-y-3">
                            <!-- Item de Vacuna 1 -->
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Vacuna</span>
                                    <h5 class="font-bold text-slate-800 text-sm mt-1">Cemvac (Derriengue y Carbón Sintomático)</h5>
                                    <p class="text-xs text-slate-500">Aplicado por: <strong>Dr. Ramírez</strong> | Lote: #CV-9921</p>
                                </div>
                                <div class="text-right sm:text-right w-full sm:w-auto">
                                    <span class="text-xs font-medium text-slate-600 block">10 Jun, 2026</span>
                                    <span class="text-xs text-emerald-700 font-semibold">Aplicado con éxito</span>
                                </div>
                            </div>

                            <!-- Item de Medicamento 2 -->
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                                <div>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-teal-100 text-teal-800">Medicamento</span>
                                    <h5 class="font-bold text-slate-800 text-sm mt-1">Ivermectina 1% (Desparasitante)</h5>
                                    <p class="text-xs text-slate-500">Dosis: <strong>10 ml (Vía subcutánea)</strong></p>
                                </div>
                                <div class="text-right sm:text-right w-full sm:w-auto">
                                    <span class="text-xs font-medium text-slate-600 block">15 May, 2026</span>
                                    <span class="text-xs text-emerald-700 font-semibold">Aplicado con éxito</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
