<?php

namespace App\Http\Requests\Proveedor;

use Illuminate\Foundation\Http\FormRequest;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Tolerancia a errata histórica 'nombreProoveedor'
        if ($this->has('nombreProoveedor') && !$this->has('nombreProveedor')) {
            $this->merge([
                'nombreProveedor' => $this->input('nombreProoveedor'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nombreProveedor' => 'required|string|max:255',
            'nombreContacto'  => 'nullable|string|max:255',
            'telefono'        => 'nullable|string|max:50',
            'lugar'           => 'nullable|string|max:255',
            'razon_social'    => 'nullable|string|max:255',
            'ubicacion'       => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nombreProveedor.required' => 'El nombre del proveedor o rancho es obligatorio.',
            'nombreProveedor.max'      => 'El nombre no puede superar los 255 caracteres.',
        ];
    }
}
