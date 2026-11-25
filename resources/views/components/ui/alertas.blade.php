{{-- Mensajes de las vistas de módulos (session success/error y errores de validación) --}}
@if (session('success'))
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><i class="fa-solid fa-circle-check mt-0.5"></i> {{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-semibold"><i class="fa-solid fa-circle-exclamation"></i> Revisa lo siguiente:</p>
        <ul class="mt-1 list-inside list-disc text-xs">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
