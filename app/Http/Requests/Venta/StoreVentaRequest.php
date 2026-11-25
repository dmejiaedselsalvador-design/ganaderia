<?php

namespace App\Http\Requests\Venta;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id'                 => 'nullable|exists:clientes,id',
            'cliente_nombre'             => 'required_without:cliente_id|nullable|string|max:255',
            'condicion_pago'             => 'nullable|in:contado,credito',
            'forma_pago'                 => 'nullable|string|max:30',
            'dias_credito'               => 'nullable|integer|min:1|max:365',
            'folio_cfdi'                 => 'nullable|string|max:60',
            'cliente_telefono'           => 'nullable|string|max:50',
            'cliente_identificacion'     => 'nullable|string|max:100',
            'destino'                    => 'nullable|string|max:255',
            'fecha_venta'                => 'required|date',
            'modalidad_pago'             => 'required|in:por_kilo,precio_fijo',
            'descuento'                  => 'nullable|numeric|min:0',
            'notas'                      => 'nullable|string|max:1000',
            'animales'                   => 'required|array|min:1',
            'animales.*.ganado_id'       => 'required|integer|exists:ganado,GanadoID|distinct',
            'animales.*.peso_venta'      => 'required|numeric|min:1',
            'animales.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_nombre.required_without'     => 'Selecciona un cliente registrado o escribe el nombre del comprador.',
            'fecha_venta.required'                => 'La fecha de venta es obligatoria.',
            'modalidad_pago.required'             => 'La modalidad de cobro es obligatoria.',
            'animales.required'                   => 'Debes incluir al menos un animal en la venta.',
            'animales.min'                        => 'Debes incluir al menos un animal en la venta.',
            'animales.*.ganado_id.required'       => 'El identificador del animal es obligatorio.',
            'animales.*.ganado_id.distinct'       => 'No puedes agregar el mismo animal dos veces a la venta.',
            'animales.*.peso_venta.required'      => 'El peso de venta es obligatorio para cada animal.',
            'animales.*.peso_venta.min'           => 'El peso de venta debe ser mayor a 0 kg.',
            'animales.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
            'animales.*.precio_unitario.min'      => 'El precio no puede ser menor a $0.00 MXN.',
        ];
    }
}
