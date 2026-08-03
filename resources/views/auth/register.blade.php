<x-app-layout>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"></div>
  <form method="POST" action="{{ route('register') }}" class="space-y-5 bg-amber-50/50 p-6 rounded-2xl border border-amber-200 shadow-sm">
    @csrf

    <!-- Encabezado Temático -->
    <div class="text-center mb-6">
        <span class="text-3xl">🌾</span>
        <h2 class="text-xl font-bold text-emerald-900 mt-1">Registro de Nuevo Personal / Operador</h2>
        <p class="text-xs text-emerald-700">Sistema de Control Ganadero</p>
    </div>

    <!-- Name -->
    <div>
        <x-input-label for="name" :value="__('Nombre Completo')" class="text-emerald-900 font-semibold" />
        <x-text-input id="name" class="block mt-1 w-full border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Ej. Juan Pérez" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <!-- Email Address -->
    <div>
        <x-input-label for="email" :value="__('Correo Electrónico')" class="text-emerald-900 font-semibold" />
        <x-text-input id="email" class="block mt-1 w-full border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="correo@ganaderia.com" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Password -->
    <div>
        <x-input-label for="password" :value="__('Contraseña')" class="text-emerald-900 font-semibold" />
        <x-text-input id="password" class="block mt-1 w-full border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl"
                        type="password"
                        name="password"
                        required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Confirm Password -->
    <div>
        <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="text-emerald-900 font-semibold" />
        <x-text-input id="password_confirmation" class="block mt-1 w-full border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-xl"
                        type="password"
                        name="password_confirmation" required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <!-- Acciones -->
    <div class="flex items-center justify-between pt-2">
        <a class="underline text-sm text-emerald-800 hover:text-emerald-600 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" href="{{ route('login') }}">
            {{ __('¿Ya estás registrado?') }}
        </a>

        <x-primary-button class="bg-emerald-700 hover:bg-emerald-800 focus:bg-emerald-900 active:bg-emerald-950 focus:ring-emerald-500 transition-colors px-5 py-2.5 rounded-xl text-white font-semibold shadow-md">
            🚜 {{ __('Registrar Cuenta') }}
        </x-primary-button>
    </div>
</form>

</div>
        </div>
    </div>
</x-app-layout>
