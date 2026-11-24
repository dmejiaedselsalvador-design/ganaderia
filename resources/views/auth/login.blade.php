<x-guest-layout>
    <!-- Tarjeta Principal de Inicio de Sesión -->
    <div class="bg-white rounded-3xl shadow-xl shadow-stone-200/70 border border-stone-200/80 p-8 sm:p-10 space-y-6">

        <!-- Encabezado de la Tarjeta -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    Acceso al Sistema
                </span>
                <span class="text-xs text-stone-400 font-medium">Panel de Control</span>
            </div>

            <h2 class="font-serif-brand text-2xl sm:text-3xl font-bold tracking-tight text-stone-900">
                Iniciar Sesión
            </h2>
            <p class="mt-1 text-xs sm:text-sm text-stone-500">
                Ingrese sus credenciales de usuario para acceder a la gestión ganadera.
            </p>
        </div>

        <!-- Estado de Sesión -->
        <x-auth-session-status class="mb-2" :status="session('status')" />

        <!-- Alerta de Errores de Validación -->
        @if ($errors->any())
            <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-xs shadow-2xs">
                <div class="font-semibold flex items-center gap-1.5 mb-1 text-sm text-rose-900">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i> No fue posible ingresar
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-stone-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de Acceso -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Correo Electrónico -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-1.5">
                    Correo Electrónico <span class="text-emerald-700">*</span>
                </label>
                <div class="relative rounded-xl shadow-2xs">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fa-regular fa-envelope text-sm"></i>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="username"
                           placeholder="ejemplo@rancho.com"
                           class="block w-full rounded-xl border border-stone-300 bg-white py-3 pl-10 pr-4 text-sm text-stone-900 placeholder-stone-400 transition focus:border-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-700/10" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-rose-600" />
            </div>

            <!-- Contraseña -->
            <div x-data="{ show: false }">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-700">
                        Contraseña <span class="text-emerald-700">*</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           class="text-xs font-medium text-emerald-800 hover:text-emerald-900 hover:underline transition">
                            ¿Olvidó su contraseña?
                        </a>
                    @endif
                </div>

                <div class="relative rounded-xl shadow-2xs">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-stone-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input id="password" :type="show ? 'text' : 'password'" name="password"
                           required autocomplete="current-password"
                           placeholder="••••••••••••"
                           class="block w-full rounded-xl border border-stone-300 bg-white py-3 pl-10 pr-11 text-sm text-stone-900 placeholder-stone-400 transition focus:border-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-700/10" />

                    <!-- Botón Mostrar/Ocultar Contraseña -->
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-stone-400 hover:text-stone-600 transition"
                            aria-label="Mostrar u ocultar contraseña">
                        <i x-show="!show" class="fa-regular fa-eye text-sm"></i>
                        <i x-show="show" x-cloak class="fa-regular fa-eye-slash text-sm text-emerald-700"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-rose-600" />
            </div>

            <!-- Recordar Sesión -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="h-4 w-4 rounded border-stone-300 text-emerald-800 focus:ring-emerald-700/30 transition cursor-pointer" />
                    <span class="ml-2.5 text-xs sm:text-sm text-stone-600 font-medium">
                        Mantener sesión iniciada
                    </span>
                </label>

                <span class="text-xs text-stone-400 hidden sm:inline-flex items-center gap-1">
                    <i class="fa-solid fa-shield text-[10px] text-emerald-600"></i> Seguro
                </span>
            </div>

            <!-- Botón de Envío -->
            <div class="pt-2">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#05201a] hover:bg-emerald-950 text-amber-200 hover:text-white px-6 py-3.5 text-sm font-semibold uppercase tracking-wider transition-all duration-200 shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-800/20 active:scale-[0.99]">
                    <span>Ingresar al Sistema</span>
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                </button>
            </div>
        </form>

        <!-- Pie de Seguridad Discreto -->
        <div class="pt-4 border-t border-stone-100 flex items-center justify-center gap-2 text-xs text-stone-400">
            <i class="fa-solid fa-shield-halved text-emerald-600 text-xs"></i>
            <span>Conexión protegida · Ganadería Taurino</span>
        </div>

    </div>
</x-guest-layout>
