@php
    $tema = request()->cookie('tema');
    $tema = in_array($tema, ['claro', 'oscuro', 'metal'], true) ? $tema : 'claro';
    $temaClass = $tema === 'oscuro' ? 'tema-oscuro tema-dark' : 'tema-' . $tema;
    $plantas = \App\Support\Plantas::todas();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('plantas.grupo') }} · Acceso al Sistema</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
            .font-serif-brand { font-family: 'Playfair Display', Georgia, serif; }
            .panel-grupo { background: radial-gradient(1200px 600px at -10% -10%, #0b3b2e 0%, transparent 55%),
                                       radial-gradient(900px 600px at 110% 110%, #4a2f0a 0%, transparent 55%),
                                       #0b0f14; }
            body.tema-metal .panel-grupo { background: linear-gradient(160deg, #3a4553, #1b2129); }
        </style>
    </head>
    <body class="min-h-screen antialiased selection:bg-emerald-600 selection:text-white {{ $temaClass }}" data-tema="{{ $tema }}">

        <div class="min-h-screen grid lg:grid-cols-12">

            <!-- ============ PANEL DEL GRUPO (escritorio) ============ -->
            <aside class="panel-grupo relative hidden lg:flex lg:col-span-5 flex-col justify-between overflow-hidden p-12 xl:p-14 text-white/80">
                <div class="pointer-events-none absolute inset-0 opacity-[0.05]"
                     style="background-image: linear-gradient(to right, #fff 1px, transparent 1px), linear-gradient(to bottom, #fff 1px, transparent 1px); background-size: 48px 48px;"></div>

                <!-- Marca del grupo -->
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-300/30 bg-amber-300/10 text-amber-300">
                        <i class="fa-solid fa-layer-group text-xl"></i>
                    </div>
                    <div class="leading-tight">
                        <span class="block font-serif-brand text-xl font-bold tracking-wide text-white">{{ config('plantas.grupo') }}</span>
                        <span class="block text-[10px] uppercase tracking-[0.28em] text-white/55 font-semibold">Plataforma empresarial unificada</span>
                    </div>
                </div>

                <!-- Propuesta -->
                <div class="relative z-10 my-auto max-w-lg space-y-8 py-10">
                    <div>
                        <div class="mb-4 h-0.5 w-14 rounded-full bg-gradient-to-r from-amber-300 to-transparent"></div>
                        <h1 class="font-serif-brand text-3xl xl:text-4xl font-bold leading-tight text-white">
                            Un solo acceso para <span class="text-amber-300">todas tus empresas</span>.
                        </h1>
                        <p class="mt-4 text-sm leading-relaxed text-white/55">
                            Ingresa una vez y trabaja en la planta que te corresponde. Cada emprendimiento mantiene su información, sus indicadores y su equipo por separado.
                        </p>
                    </div>

                    <div class="space-y-3">
                        @foreach ($plantas as $p)
                            <div class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-lg" style="background: {{ $p['acento'] }}">
                                    <i class="fa-solid {{ $p['icono'] }}"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold uppercase tracking-wider text-white">{{ $p['nombre'] }}</h4>
                                    <p class="mt-0.5 text-xs text-white/55">{{ $p['descripcion'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-5 text-xs text-white/40">
                    <span>&copy; {{ date('Y') }} {{ config('plantas.grupo') }}</span>
                    <span class="flex items-center gap-2 font-medium text-emerald-400/80">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Sistema operativo
                    </span>
                </div>
            </aside>

            <!-- ============ FORMULARIO ============ -->
            <main class="main-content-area relative lg:col-span-7 flex items-center justify-center p-6 sm:p-10 lg:p-14">

                <!-- Selector de tema -->
                <div class="absolute right-4 top-4 flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-sm">
                    @foreach (['claro' => 'fa-sun', 'oscuro' => 'fa-moon', 'metal' => 'fa-gears'] as $clave => $icono)
                        <button type="button" data-tema-btn="{{ $clave }}" onclick="window.cambiarTema('{{ $clave }}')" title="Tema {{ ucfirst($clave) }}"
                            class="flex h-8 w-9 items-center justify-center rounded-lg text-xs transition {{ $tema === $clave ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500' }}">
                            <i class="fa-solid {{ $icono }}"></i>
                        </button>
                    @endforeach
                </div>

                <div class="w-full max-w-md">
                    <!-- Encabezado móvil -->
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-amber-300 shadow-sm">
                            <i class="fa-solid fa-layer-group text-lg"></i>
                        </div>
                        <div>
                            <span class="block font-serif-brand text-lg font-bold leading-tight text-stone-900">{{ config('plantas.grupo') }}</span>
                            <span class="block text-[10px] font-semibold uppercase tracking-wider text-stone-500">
                                {{ $plantas->pluck('nombre')->implode(' · ') }}
                            </span>
                        </div>
                    </div>

                    {{ $slot }}

                    <p class="mt-8 text-center text-xs text-stone-400 lg:hidden">
                        &copy; {{ date('Y') }} {{ config('plantas.grupo') }} · Acceso seguro
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
