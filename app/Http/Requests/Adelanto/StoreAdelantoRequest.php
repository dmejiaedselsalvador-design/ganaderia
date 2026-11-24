<?php

namespace App\Http\Requests\Adelanto;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdelantoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id'   => 'required|exists:proveedorGanado,id',
            'advance_date'  => 'required|date',
            'concept'       => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required'  => 'El identificador del proveedor es obligatorio.',
            'supplier_id.exists'    => 'El proveedor seleccionado no existe.',
            'advance_date.required' => 'La fecha del adelanto es obligatoria.',
            'advance_date.date'     => 'Ingresa una fecha válida.',
            'concept.required'      => 'El concepto o descripción es obligatorio.',
            'amount.required'       => 'El monto del adelanto es obligatorio.',
            'amount.numeric'        => 'El monto debe ser un valor numérico.',
            'amount.min'            => 'El monto debe ser mayor a cero.',
        ];
    }
}
