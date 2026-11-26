@php $cat = array_combine(\App\Models\Ganaderia\GastoOperativo::CATEGORIAS, \App\Models\Ganaderia\GastoOperativo::CATEGORIAS);
     $fp = array_combine(\App\Models\Ganaderia\GastoOperativo::FORMAS_PAGO, \App\Models\Ganaderia\GastoOperativo::FORMAS_PAGO); @endphp
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <x-ui.field label="Fecha" name="fecha" type="date" :value="optional($gasto->fecha)->format('Y-m-d') ?? date('Y-m-d')" required />
    <x-ui.field label="Categoría" name="categoria" type="select" :options="$cat" :value="$gasto->categoria" required />
    <x-ui.field label="Monto ($ MXN)" name="monto" type="number" step="0.01" :value="$gasto->monto" required />
    <x-ui.field label="Forma de pago" name="forma_pago" type="select" :options="$fp" :value="$gasto->forma_pago" />
    <x-ui.field label="Concepto" name="concepto" :value="$gasto->concepto" required colspan="sm:col-span-2" placeholder="Ej. Nómina semana 47, diésel tractor, reparación de comederos" />
    <x-ui.field label="Proveedor / Beneficiario" name="proveedor" :value="$gasto->proveedor" />
    <x-ui.field label="Folio CFDI" name="folio_cfdi" :value="$gasto->folio_cfdi" placeholder="UUID o folio" />
    <x-ui.field label="Asignar a corral" name="corral_lote_id" type="select" :options="$corrales" :value="$gasto->corral_lote_id" placeholder="General (se reparte entre todo el ganado)"
                ayuda="Si el gasto es de un lote específico, asígnalo; si no, se reparte por cabeza-día entre todos los animales." colspan="sm:col-span-2" />
    <div class="-mt-1 sm:col-span-2"><x-ui.atajo tipo="corral" target="select[name=corral_lote_id]" texto="+ Crear corral" /></div>
    <x-ui.field label="Ubicación" name="ubicacion_id" type="select" :options="$ubicaciones" :value="$gasto->ubicacion_id" />
    <x-ui.field label="Notas" name="notas" :value="$gasto->notas" />
</div>
