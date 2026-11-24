<?php

namespace App\Http\Requests\Factura;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id'  => 'required|exists:proveedorGanado,id',
            'fecha_factura' => 'required|date',
            'factura'       => 'required|string|max:255|unique:facturaGanado,numeroFactura',
            'observaciones' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required'  => 'Debes seleccionar un proveedor.',
            'proveedor_id.exists'    => 'El proveedor seleccionado no existe.',
            'fecha_factura.required' => 'La fecha de la factura es obligatoria.',
            'fecha_factura.date'     => 'Ingresa una fecha válida.',
            'factura.required'       => 'El número de factura o guía es obligatorio.',
            'factura.unique'         => 'El número de factura o guía ya se encuentra registrado.',
        ];
    }
}
