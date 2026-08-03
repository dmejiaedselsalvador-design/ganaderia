<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Ganadería</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-emerald-900 via-emerald-800 to-amber-950 min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">

            <!-- Logo / Icono Superior -->
            <div class="mb-4 text-center">
                <a href="/" class="inline-block p-3 bg-white/10 backdrop-blur-md rounded-2xl shadow-lg border border-white/20 transition-transform hover:scale-105">
                    <x-application-logo class="w-16 h-16 fill-current text-amber-300" />
                </a>
            </div>

            <!-- Contenedor Principal Estilo Tarjeta Ganadera -->
            <div class="w-full sm:max-w-md mt-2 px-6 py-8 bg-white/95 backdrop-blur-md shadow-2xl overflow-hidden sm:rounded-2xl border border-emerald-100">
                {{ $slot }}
            </div>

            <!-- Pie de página sutil -->
            <div class="mt-6 text-center text-xs text-emerald-200/70">
                Sistema de Control Ganadero &copy; {{ date('Y') }}
            </div>
        </div>
    </body>
</html>
