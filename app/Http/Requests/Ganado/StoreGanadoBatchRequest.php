<?php

namespace App\Http\Requests\Ganado;

use Illuminate\Foundation\Http\FormRequest;

class StoreGanadoBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalizar factura-id a factura_id si viene en kebab-case
        if ($this->has('factura-id') && !$this->has('factura_id')) {
            $this->merge(['factura_id' => $this->input('factura-id')]);
        }
    }

    public function rules(): array
    {
        return [
            'factura_id'             => 'required|numeric|exists:facturaGanado,id',
            'purchase_date'          => 'nullable|date',
            'animals'                => 'required|array|min:1',
            'animals.*.tag_number'   => 'required|string|distinct|unique:ganado,areteID',
            'animals.*.category'     => 'required|in:Becerro,Becerra,Vaca,Vaquilla,Toro,Torete',
            'animals.*.gender'       => 'nullable|in:Macho,Hembra',
            'animals.*.weight'       => 'required|numeric|min:0.01',
            'animals.*.unit_price'   => 'required|numeric|min:0',
            'animals.*.total'        => 'nullable|numeric|min:0',
            'animals.*.breed'        => 'nullable|string|max:255',
            'animals.*.observation'  => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'factura_id.required'           => 'La factura o guía asociada es obligatoria.',
            'factura_id.exists'             => 'La factura seleccionada no existe en el sistema.',
            'animals.required'              => 'Debes registrar al menos un animal en el lote.',
            'animals.min'                   => 'Debes registrar al menos un animal en el lote.',
            'animals.*.tag_number.required' => 'El número de arete es obligatorio para cada animal.',
            'animals.*.tag_number.unique'   => 'El arete :input ya se encuentra registrado.',
            'animals.*.tag_number.distinct' => 'Hay aretes duplicados en este mismo lote.',
            'animals.*.category.required'   => 'La categoría del animal es obligatoria.',
            'animals.*.category.in'         => 'La categoría seleccionada no es válida.',
            'animals.*.weight.required'     => 'El peso del animal es obligatorio.',
            'animals.*.weight.min'          => 'El peso debe ser mayor a 0.',
            'animals.*.unit_price.required' => 'El precio por kilo o unidad es obligatorio.',
        ];
    }
}
