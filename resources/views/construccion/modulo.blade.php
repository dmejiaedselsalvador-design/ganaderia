<x-app-layout>
    <div class="mx-auto max-w-3xl">
        <div class="rounded-3xl border border-slate-100 bg-white p-10 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-2xl text-amber-600">
                <i class="fa-solid {{ $modulo[1] }}"></i>
            </div>
            <p class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Construcción · Próximamente</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $modulo[0] }}</h1>
            <p class="mx-auto mt-3 max-w-md text-sm text-slate-500">{{ $modulo[2] }}</p>
            <a href="{{ route('construccion.dashboard') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                <i class="fa-solid fa-arrow-left text-xs"></i> Volver al dashboard
            </a>
        </div>
    </div>
</x-app-layout>
