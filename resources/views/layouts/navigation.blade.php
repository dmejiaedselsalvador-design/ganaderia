<nav class="h-16 bg-white shadow-xs border-b border-slate-200 flex items-center justify-between px-6 z-10">
    <div class="flex items-center gap-4">
        <!-- Botón Hamburguesa para Móviles -->
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-600 hover:text-slate-900 focus:outline-none">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <h1 class="text-lg sm:text-xl font-bold text-slate-800 truncate">
            @if (isset($header))
                {{ $header }}
            @else
                Panel de Control de Ganado
            @endif
        </h1>
    </div>

    <!-- Menú de Usuario y Ajustes (Dropdown original de Laravel adaptado) -->
    <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-slate-600 hidden sm:inline">Rancho San José</span>

        <div class="relative" x-data="{ dropdownOpen: false }">
            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 focus:outline-none">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold border border-emerald-200 shadow-xs">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 border border-slate-200 z-50" style="display: none;">
                <div class="px-4 py-2 border-b border-slate-100">
                    <p class="text-xs text-slate-500">Conectado como</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                </div>

                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                    {{ __('Profile') }}
                </a>

                <!-- Autenticación / Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-slate-100">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
