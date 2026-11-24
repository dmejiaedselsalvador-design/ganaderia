<?php

namespace App\Http\Requests\Ganado;

use Illuminate\Foundation\Http\FormRequest;

class StorePesajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ganado_id'     => 'required|integer|exists:ganado,GanadoID',
            'peso'          => 'required|numeric|min:1',
            'fecha_pesaje'  => 'required|date|before_or_equal:today',
            'bascula'       => 'nullable|string|max:100',
            'lote_corral'   => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'ganado_id.required'          => 'El animal es obligatorio.',
            'ganado_id.exists'            => 'El animal seleccionado no existe.',
            'peso.required'               => 'El peso en báscula es obligatorio.',
            'peso.min'                    => 'El peso debe ser mayor a cero.',
            'fecha_pesaje.required'       => 'La fecha de pesaje es obligatoria.',
            'fecha_pesaje.before_or_equal'=> 'La fecha de pesaje no puede ser en el futuro.',
        ];
    }
}
