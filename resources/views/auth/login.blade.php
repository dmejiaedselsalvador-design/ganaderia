<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

   <form method="POST" action="{{ route('login') }}" class="space-y-5 bg-amber-50/50 p-6 rounded-2xl border border-amber-200 shadow-sm">
    @csrf

    <!-- Encabezado Temático -->
    <div class="text-center mb-6">
        <span class="text-3xl">🐂</span>
        <h2 class="text-xl font-bold text-emerald-900 mt-1">Control Ganadero</h2>
        <p class="text-xs text-emerald-700">Inicia sesión para gestionar el sistema</p>
    </div>

    <!-- Email Address -->
    <div>
        <x-input-label for="email" :value="__('Correo Electrónico')" class="text-emerald-900 font-semibold" />
        <x-text-input id="email" class="block mt-1 w-full border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="correo@ganaderia.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Password -->
    <div>
        <x-input-label for="password" :value="__('Contraseña')" class="text-emerald-900 font-semibold" />
        <x-text-input id="password" class="block mt-1 w-full border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl"
                        type="password"
                        name="password"
                        required autocomplete="current-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Remember Me -->
    <div class="block mt-4">
        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-emerald-300 text-emerald-700 shadow-sm focus:ring-emerald-500" name="remember">
            <span class="ml-2 text-sm text-emerald-900">{{ __('Recordar mi cuenta') }}</span>
        </label>
    </div>

    <!-- Acciones -->
    <div class="flex items-center justify-between pt-2">
        @if (Route::has('password.request'))
            <a class="underline text-sm text-emerald-800 hover:text-emerald-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" href="{{ route('password.request') }}">
                {{ __('¿Olvidaste tu contraseña?') }}
            </a>
        @endif

        <x-primary-button class="bg-emerald-700 hover:bg-emerald-800 focus:bg-emerald-900 active:bg-emerald-950 focus:ring-emerald-500 transition-colors px-5 py-2.5 rounded-xl text-white font-semibold shadow-md ml-3">
            🚪 {{ __('Ingresar') }}
        </x-primary-button>
    </div>
</form>
</x-guest-layout>
