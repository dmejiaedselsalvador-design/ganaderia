<?php

namespace App\Models\Engorda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RacionAlimento extends Model
{
    use HasFactory;

    protected $table = 'raciones_alimento';

    protected $fillable = [
        'codigo',
        'nombre',
        'fase',
        'costo_por_kg',
        'materia_seca_pct',
        'proteina_cruda_pct',
        'energia_metabolizable_mcal',
        'descripcion_ingredientes',
        'activo',
    ];

    protected $casts = [
        'costo_por_kg'               => 'decimal:2',
        'materia_seca_pct'           => 'decimal:2',
        'proteina_cruda_pct'         => 'decimal:2',
        'energia_metabolizable_mcal' => 'decimal:2',
        'activo'                     => 'boolean',
    ];

    public function consumos(): HasMany
    {
        return $this->hasMany(ConsumoAlimento::class, 'racion_id', 'id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
