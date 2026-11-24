<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ganadería Taurino') }} · Control Ganadero Integral</title>

        <!-- Fonts: Inter & Playfair Display (Idénticas al interior del sistema) -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700&display=swap" rel="stylesheet" />

        <!-- FontAwesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }
            .font-serif-brand {
                font-family: 'Playfair Display', Georgia, serif;
            }
        </style>
    </head>
    <body class="min-h-screen bg-stone-100 text-stone-800 antialiased selection:bg-emerald-600 selection:text-white">

        <div class="min-h-screen grid lg:grid-cols-12">

            <!-- ========================================== -->
            <!-- PANEL LATERAL DE MARCA (SOLO ESCRITORIO)    -->
            <!-- ========================================== -->
            <aside class="relative hidden lg:flex lg:col-span-5 xl:col-span-5 flex-col justify-between overflow-hidden bg-[#05201a] p-12 xl:p-14 text-emerald-100/80">

                <!-- Decoración ambiental sutil idéntica a la barra lateral del sistema -->
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -top-32 -left-28 h-96 w-96 rounded-full bg-emerald-500/15 blur-3xl"></div>
                    <div class="absolute -bottom-36 -right-28 h-96 w-96 rounded-full bg-amber-400/10 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.04]"
                         style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 48px 48px;"></div>
                    <div class="absolute inset-y-0 right-0 w-px bg-gradient-to-b from-transparent via-amber-300/30 to-transparent"></div>
                </div>

                <!-- Cabecera de Marca -->
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-300/30 bg-amber-300/10 text-amber-300 shadow-xs">
                            <i class="fa-solid fa-cow text-xl"></i>
                        </div>
                        <div class="leading-tight">
                            <span class="block font-serif-brand text-xl font-bold tracking-wide text-white">
                                Ganadería<span class="text-amber-300">System</span>
                            </span>
                            <span class="block text-[10px] uppercase tracking-[0.28em] text-emerald-200/50 font-semibold">
                                Rancho Taurino · Control Integral
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Bloque Central: Propuesta de Valor y Características Reales del Sistema -->
                <div class="relative z-10 my-auto py-10 max-w-lg space-y-8">
                    <div>
                        <div class="mb-4 h-0.5 w-14 bg-gradient-to-r from-amber-300 to-transparent rounded-full"></div>
                        <h1 class="font-serif-brand text-3xl xl:text-4xl font-bold text-white leading-tight">
                            Gestión ganadera con <span class="text-amber-300">precisión</span>, control y rentabilidad.
                        </h1>
                        <p class="mt-4 text-sm leading-relaxed text-emerald-100/70">
                            Plataforma integral diseñada para la toma de decisiones basada en datos reales: báscula, pesaje, inventario activo, compras, ventas y bajas del hato.
                        </p>
                    </div>

                    <!-- Módulos Clave del Sistema -->
                    <div class="space-y-3 pt-2">
                        <!-- Módulo 1 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0 text-sm mt-0.5">
                                <i class="fa-solid fa-weight-scale"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Control de Báscula y GDP</h4>
                                <p class="text-xs text-emerald-100/60 mt-0.5">
                                    Historial individual de pesajes y cálculo automático de ganancia diaria de peso.
                                </p>
                            </div>
                        </div>

                        <!-- Módulo 2 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                            <div class="w-8 h-8 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center shrink-0 text-sm mt-0.5">
                                <i class="fa-solid fa-bullseye"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Reglas de Venta por Categoría</h4>
                                <p class="text-xs text-emerald-100/60 mt-0.5">
                                    Alertas automáticas cuando el ganado alcanza el peso objetivo de mercado.
                                </p>
                            </div>
                        </div>

                        <!-- Módulo 3 -->
                        <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                            <div class="w-8 h-8 rounded-lg bg-teal-400/20 text-teal-300 flex items-center justify-center shrink-0 text-sm mt-0.5">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Compras, Ventas y Liquidación</h4>
                                <p class="text-xs text-emerald-100/60 mt-0.5">
                                    Manejo de proveedores, amortización de adelantos y comprobantes en moneda nacional.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie del Panel Lateral -->
                <div class="relative z-10 flex items-center justify-between text-xs text-emerald-200/40 border-t border-white/10 pt-5">
                    <span>&copy; {{ date('Y') }} Rancho Taurino</span>
                    <span class="flex items-center gap-2 text-emerald-300/80 font-medium">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Sistema Operativo
                    </span>
                </div>
            </aside>

            <!-- ========================================== -->
            <!-- PANEL DERECHO: FORMULARIO LIMPIO Y SERIO    -->
            <!-- ========================================== -->
            <main class="lg:col-span-7 xl:col-span-7 flex items-center justify-center p-6 sm:p-10 lg:p-14 bg-stone-100/80">
                <div class="w-full max-w-md">

                    <!-- Encabezado Móvil (Sólo visible en móviles y tabletas) -->
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#05201a] text-amber-300 shadow-sm">
                            <i class="fa-solid fa-cow text-lg"></i>
                        </div>
                        <div>
                            <span class="block font-serif-brand text-lg font-bold text-stone-900 leading-tight">
                                Ganadería<span class="text-emerald-700">System</span>
                            </span>
                            <span class="block text-[10px] uppercase tracking-wider text-stone-500 font-semibold">
                                Rancho Taurino · Control Integral
                            </span>
                        </div>
                    </div>

                    <!-- Contenedor del Formulario (Tarjeta Blanca Elevada) -->
                    {{ $slot }}

                    <!-- Pie Móvil -->
                    <p class="mt-8 text-center text-xs text-stone-400 lg:hidden">
                        &copy; {{ date('Y') }} GanaderíaSystem · Acceso Seguro
                    </p>
                </div>
            </main>

        </div>

    </body>
</html>
