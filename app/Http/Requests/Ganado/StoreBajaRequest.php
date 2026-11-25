<?php

namespace App\Http\Requests\Ganado;

use Illuminate\Foundation\Http\FormRequest;

class StoreBajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ganado_id'     => 'required|integer|exists:ganado,GanadoID',
            'fecha_muerte'  => 'required|date|before_or_equal:today',
            'causa_muerte'  => 'required|string|max:150',
            'necropsia'     => 'nullable|string|max:1000',
            'observaciones' => 'nullable|string|max:1000',
            'tipo_baja'     => 'nullable|in:Muerte,Sacrificio,Robo,Extravio,Descarte',
            'valor_recuperado_mxn' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'ganado_id.required'           => 'El animal es obligatorio.',
            'fecha_muerte.required'        => 'La fecha de defunción es obligatoria.',
            'fecha_muerte.before_or_equal' => 'La fecha no puede ser futura.',
            'causa_muerte.required'        => 'Debes especificar la causa de muerte o diagnóstico preliminar.',
        ];
    }
}
