@php $editando = $cliente->exists; $reg = collect(\App\Models\Ganaderia\Cliente::REGIMENES)->mapWithKeys(fn ($t, $k) => [$k => "$k · $t"])->all(); @endphp
<x-app-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <x-ui.header :titulo="$editando ? 'Editar cliente' : 'Nuevo cliente'" subtitulo="Los datos fiscales se usan para emitir el CFDI 4.0 de la venta." icono="fa-address-book">
            <x-slot name="acciones"><x-ui.btn :href="$editando ? route('clientes.show', $cliente) : route('clientes.index')" variante="secundario" icono="fa-arrow-left">Volver</x-ui.btn></x-slot>
        </x-ui.header>
        <x-ui.alertas />

        <form method="POST" action="{{ $editando ? route('clientes.update', $cliente) : route('clientes.store') }}" class="space-y-6">
            @csrf @if ($editando) @method('PUT') @endif
            <x-ui.card titulo="Datos generales">
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.field label="Nombre comercial" name="nombre" :value="$cliente->nombre" required />
                    <x-ui.field label="Contacto" name="contacto" :value="$cliente->contacto" />
                    <x-ui.field label="Teléfono" name="telefono" :value="$cliente->telefono" placeholder="662-000-0000" />
                    <x-ui.field label="Correo" name="email" type="email" :value="$cliente->email" />
                    <x-ui.field label="Dirección" name="direccion" type="textarea" :value="$cliente->direccion" colspan="sm:col-span-2" />
                </div>
            </x-ui.card>
            <x-ui.card titulo="Datos fiscales (SAT)">
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.field label="Razón social" name="razon_social" :value="$cliente->razon_social" ayuda="Tal como aparece en la Constancia de Situación Fiscal, sin régimen societario." />
                    <x-ui.field label="RFC" name="rfc" :value="$cliente->rfc" class="uppercase font-mono" placeholder="XAXX010101000" />
                    <x-ui.field label="Régimen fiscal" name="regimen_fiscal" type="select" :options="$reg" :value="$cliente->regimen_fiscal" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field label="Uso CFDI" name="uso_cfdi" :value="$cliente->uso_cfdi" placeholder="G01" />
                        <x-ui.field label="C.P. fiscal" name="codigo_postal" :value="$cliente->codigo_postal" placeholder="83000" />
                    </div>
                </div>
            </x-ui.card>
            <x-ui.card titulo="Crédito">
                <div class="grid gap-3 sm:grid-cols-3">
                    <x-ui.field label="Días de crédito" name="dias_credito" type="number" :value="$cliente->dias_credito" ayuda="0 = solo contado" />
                    <x-ui.field label="Límite de crédito ($)" name="limite_credito" type="number" step="0.01" :value="$cliente->limite_credito" ayuda="0 = sin límite" />
                    @if ($editando)
                        <label class="flex items-end gap-2 pb-2 text-sm text-slate-600"><input type="checkbox" name="activo" value="1" @checked($cliente->activo) class="rounded border-slate-300 text-emerald-600"> Cliente activo</label>
                    @endif
                    <x-ui.field label="Notas" name="notas" type="textarea" :value="$cliente->notas" colspan="sm:col-span-3" />
                </div>
            </x-ui.card>
            <x-ui.btn icono="fa-floppy-disk">{{ $editando ? 'Guardar cambios' : 'Registrar cliente' }}</x-ui.btn>
        </form>
    </div>
</x-app-layout>
