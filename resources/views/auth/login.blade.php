<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <!-- Encabezado -->
    <div class="mb-10">
        <div class="mb-5 h-px w-12 bg-gradient-to-r from-amber-500 to-transparent"></div>
        <h2 class="font-['Playfair_Display'] text-4xl font-semibold tracking-tight text-[#05201a]">
            Bienvenido
        </h2>
        <p class="mt-3 text-sm text-stone-500">
            Ingrese sus credenciales para acceder al panel de control.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email -->
        <div>
            <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-stone-600">
                Correo electrónico
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username"
                       placeholder="correo@ganaderia.com"
                       class="block w-full rounded-xl border border-stone-300 bg-white py-3.5 pl-12 pr-4 text-sm text-stone-900 placeholder-stone-400 shadow-sm transition focus:border-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-800/10" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-stone-600">
                    Contraseña
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-medium text-emerald-800 transition hover:text-amber-600">
                        ¿Olvidó su contraseña?
                    </a>
                @endif
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-stone-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
                <input id="password" :type="show ? 'text' : 'password'" name="password"
                       required autocomplete="current-password"
                       placeholder="••••••••"
                       class="block w-full rounded-xl border border-stone-300 bg-white py-3.5 pl-12 pr-12 text-sm text-stone-900 placeholder-stone-400 shadow-sm transition focus:border-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-800/10" />
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-stone-400 transition hover:text-emerald-800"
                        aria-label="Mostrar u ocultar contraseña">
                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Recordarme -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                   class="h-4 w-4 rounded border-stone-300 text-emerald-800 focus:ring-emerald-800/30" />
            <label for="remember_me" class="ml-2.5 text-sm text-stone-600">
                Mantener sesión iniciada
            </label>
        </div>

        <!-- Botón -->
        <button type="submit"
                class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-[#05201a] px-5 py-4 text-sm font-semibold uppercase tracking-[0.15em] text-amber-200 shadow-lg shadow-emerald-950/20 transition-all duration-300 hover:bg-emerald-900 hover:shadow-xl hover:shadow-emerald-950/30 focus:outline-none focus:ring-4 focus:ring-emerald-800/30 active:scale-[0.99]">
            <span class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-amber-300/70 to-transparent"></span>
            Ingresar
            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </button>
    </form>

    <!-- Nota de seguridad -->
    <div class="mt-8 flex items-center justify-center gap-2 text-xs text-stone-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
        </svg>
        Acceso protegido y cifrado
    </div>
</x-guest-layout>
