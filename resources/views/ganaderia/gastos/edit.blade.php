<x-app-layout>
    <div class="mx-auto max-w-5xl space-y-6">
        <x-ui.header titulo="Gasto: {{ $gasto->concepto }}" subtitulo="{{ $gasto->categoria }} · {{ $gasto->fecha->format('d/m/Y') }}" icono="fa-receipt">
            <x-slot name="acciones"><x-ui.btn :href="route('gastos.index')" variante="secundario" icono="fa-arrow-left">Volver</x-ui.btn></x-slot>
        </x-ui.header>
        <x-ui.alertas />

        @if ($gasto->estado !== 'vigente')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><i class="fa-solid fa-ban"></i> Gasto anulado. Motivo: {{ $gasto->motivo_anulacion }}</div>
        @endif

        <x-ui.card titulo="Datos del gasto">
            <form method="POST" action="{{ route('gastos.update', $gasto) }}" class="space-y-4">
                @csrf @method('PUT')
                <fieldset @disabled($gasto->estado !== 'vigente')>
                    @include('ganaderia.gastos._form')
                </fieldset>
                @if ($gasto->estado === 'vigente')<x-ui.btn icono="fa-floppy-disk">Guardar cambios</x-ui.btn>@endif
            </form>
        </x-ui.card>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-ui.documentos :modelo="$gasto" tipo="gasto" />
            @if ($gasto->estado === 'vigente')
                <x-ui.card titulo="Anular gasto" subtitulo="No se borra: queda marcado como anulado y registrado en la bitácora.">
                    <form method="POST" action="{{ route('gastos.anular', $gasto) }}" class="space-y-3" data-confirm="¿Anular este gasto? Quedará registrado como anulado." data-peligro data-boton="Anular">
                        @csrf
                        <x-ui.field label="Motivo" name="motivo" required />
                        <x-ui.btn variante="peligro" icono="fa-ban">Anular</x-ui.btn>
                    </form>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-app-layout>
