<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Control Ganadero</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600|playfair-display:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-['Inter'] antialiased bg-stone-50 text-stone-900">
        <div class="min-h-screen grid lg:grid-cols-2">

            <!-- Panel de marca (solo escritorio) -->
            <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-[#05201a] p-14 text-stone-100">

                <!-- Fondo decorativo -->
                <div class="pointer-events-none absolute inset-0">
                    <div class="absolute -top-40 -left-40 h-[32rem] w-[32rem] rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-48 -right-32 h-[30rem] w-[30rem] rounded-full bg-amber-400/10 blur-3xl"></div>
                    <div class="absolute inset-0 opacity-[0.07]"
                         style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 56px 56px;"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/40"></div>
                </div>

                <!-- Marca -->
                <div class="relative flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-amber-300/30 bg-amber-300/10">
                        <x-application-logo class="h-6 w-6 fill-current text-amber-300" />
                    </div>
                    <span class="text-sm font-medium uppercase tracking-[0.25em] text-amber-200/90">Control Ganadero</span>
                </div>

                <!-- Mensaje principal -->
                <div class="relative max-w-md">
                    <div class="mb-6 h-px w-16 bg-gradient-to-r from-amber-300 to-transparent"></div>
                    <h1 class="font-['Playfair_Display'] text-5xl font-semibold leading-[1.1] tracking-tight">
                        Gestión ganadera con <span class="text-amber-300">precisión</span> y control total.
                    </h1>
                    <p class="mt-6 text-base leading-relaxed text-emerald-100/70">
                        Administre su hato, registros y producción desde una plataforma diseñada para quienes toman decisiones con datos.
                    </p>
                </div>

                <!-- Pie del panel -->
                <div class="relative flex items-center justify-between text-xs text-emerald-100/50">
                    <span>&copy; {{ date('Y') }} Sistema de Control Ganadero</span>
                    <span class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Conexión segura
                    </span>
                </div>
            </aside>

            <!-- Panel del formulario -->
            <main class="relative flex items-center justify-center px-6 py-12 sm:px-10">
                <div class="w-full max-w-md">

                    <!-- Marca móvil -->
                    <div class="mb-10 flex items-center gap-3 lg:hidden">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#05201a]">
                            <x-application-logo class="h-5 w-5 fill-current text-amber-300" />
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-[0.25em] text-emerald-900">Control Ganadero</span>
                    </div>

                    {{ $slot }}

                    <p class="mt-10 text-center text-xs text-stone-400 lg:hidden">
                        &copy; {{ date('Y') }} Sistema de Control Ganadero
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
