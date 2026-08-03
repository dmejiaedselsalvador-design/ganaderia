<x-app-layout>

      <div class="flex h-screen overflow-hidden">

        <!-- Sidebar / Menú Lateral -->
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-slate-300">
            <div class="flex items-center justify-center h-16 bg-slate-950 text-white font-bold text-lg gap-2">
                <i class="fa-solid fa-cow text-emerald-400"></i>
                <span>GanaderíaSystem</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <button onclick="cambiarVista('dashboard')" id="btn-dashboard" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg bg-emerald-600 text-white font-medium transition">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </button>
                <button onclick="cambiarVista('inventario')" id="btn-inventario" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition">
                    <i class="fa-solid fa-id-card fa-list-check"></i> Inventario (Aretes)
                </button>
                <button onclick="cambiarVista('pesajes')" id="btn-pesajes" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition">
                    <i class="fa-solid fa-weight-scale"></i> Pesajes & Engorda
                </button>
                <button onclick="cambiarVista('exportacion')" id="btn-exportacion" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Exportación USA
                </button>
            </nav>
            <div class="p-4 bg-slate-950 text-xs text-slate-500 text-center">
                Módulo México - EE.UU. v1.0
            </div>
        </aside>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col overflow-y-auto">



            <!-- Cuerpo Dinámico de la Página -->
            <main class="p-6 space-y-6">

                <!-- VISTA 1: DASHBOARD -->
                <div id="vista-dashboard" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total en Engorda</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1">480 Cabezas</h3>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Peso Promedio</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1">465.5 kg</h3>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Listos para Exportación</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1">125 Cabezas</h3>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-xs border border-slate-200">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Costo Alimento / Día</p>
                            <h3 class="text-2xl font-bold text-slate-800 mt-1">$14,200 MXN</h3>
                        </div>
                    </div>
                </div>

                <!-- VISTA 2: INVENTARIO (tablaAnimales) -->
                <div id="vista-inventario" class="hidden space-y-6">
                    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-slate-800">Catálogo de Animales (Aretes SINIIGA / ID)</h2>
                            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm transition">Agregar Animal</button>
                        </div>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b">
                                    <th class="p-3">ID Animal (Arete)</th>
                                    <th class="p-3">REEMO / UPP</th>
                                    <th class="p-3">Raza / Sexo</th>
                                    <th class="p-3">Peso Actual</th>
                                    <th class="p-3">Precio Estimado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm">
                                <tr>
                                    <td class="p-3 font-bold">MEX-849201</td>
                                    <td class="p-3">REEMO-9384 / UPP-08</td>
                                    <td class="p-3">Criollo / Macho</td>
                                    <td class="p-3 text-emerald-700 font-medium">480 kg</td>
                                    <td class="p-3">$24,500 MXN</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold">MEX-849202</td>
                                    <td class="p-3">REEMO-9385 / UPP-12</td>
                                    <td class="p-3">Angus / Macho</td>
                                    <td class="p-3 text-emerald-700 font-medium">510 kg</td>
                                    <td class="p-3">$27,000 MXN</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- VISTA 3: PESAJES (tablaPesaje) -->
                <div id="vista-pesajes" class="hidden space-y-6">
                    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-bold text-slate-800">Bitácora de Pesaje y Conversión</h2>
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">Registrar Pesaje</button>
                        </div>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b">
                                    <th class="p-3">ID Control</th>
                                    <th class="p-3">Arete Animal</th>
                                    <th class="p-3">Fecha Pesaje</th>
                                    <th class="p-3">Peso Actual</th>
                                    <th class="p-3">Ganancia Diaria (GMD)</th>
                                    <th class="p-3">Costo Alimento</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm">
                                <tr>
                                    <td class="p-3">#102</td>
                                    <td class="p-3 font-bold">MEX-849201</td>
                                    <td class="p-3">2026-08-01</td>
                                    <td class="p-3 font-medium">480.0 kg</td>
                                    <td class="p-3 text-emerald-600 font-semibold">+1.350 kg/día</td>
                                    <td class="p-3">$1,450 MXN</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- VISTA 4: EXPORTACIÓN A ESTADOS UNIDOS -->
                <div id="vista-exportacion" class="hidden space-y-6">
                    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-6">
                        <h2 class="text-lg font-bold text-slate-800 mb-2">Proceso de Cruce y Embarque USA</h2>
                        <p class="text-sm text-slate-500 mb-4">Lotes listos para cumplir con normativas de aduana y SENASICA / USDA.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                                <h3 class="font-bold text-slate-700">Lote Exportación #04 - Aduana Ciudad Juárez</h3>
                                <p class="text-sm text-slate-600 mt-1">Cabezas: 65</p>
                                <p class="text-sm text-slate-600">Pruebas Sanitarias: <span class="text-emerald-600 font-semibold">Aprobadas</span></p>
                                <p class="text-sm text-slate-600">Cliente Destino: Texas Cattle Co.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>

    </div>
   <!-- Script para simular la navegación entre vistas -->
    <script>
        function cambiarVista(vista) {
            // Ocultar todas las vistas
            document.getElementById('vista-dashboard').classList.add('hidden');
            document.getElementById('vista-inventario').classList.add('hidden');
            document.getElementById('vista-pesajes').classList.add('hidden');
            document.getElementById('vista-exportacion').classList.add('hidden');

            // Quitar estilos activos de los botones
            document.getElementById('btn-dashboard').className = "w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition text-slate-300";
            document.getElementById('btn-inventario').className = "w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition text-slate-300";
            document.getElementById('btn-pesajes').className = "w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition text-slate-300";
            document.getElementById('btn-exportacion').className = "w-full flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-slate-800 transition text-slate-300";

            // Mostrar la vista seleccionada y activar su botón
            document.getElementById('vista-' + vista).classList.remove('hidden');
            document.getElementById('btn-' + vista).className = "w-full flex items-center gap-3 px-4 py-2.5 rounded-lg bg-emerald-600 text-white font-medium transition";

            // Cambiar título superior
            const titulos = {
                'dashboard': 'Panel de Control de Ganado',
                'inventario': 'Gestión de Inventario y Aretes',
                'pesajes': 'Control de Pesaje y Engorda',
                'exportacion': 'Módulo de Exportación a EE. UU.'
            };
            document.getElementById('titulo-vista').innerText = titulos[vista];
        }
    </script>

</x-app-layout>
