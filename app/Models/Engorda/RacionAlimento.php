<?php

namespace App\Models\Engorda;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RacionAlimento extends Model
{
    use HasFactory, Auditable;

    protected $table = 'raciones_alimento';

    /** Rango de consumo por fase: [% PV mínimo, máximo, sugerido] en base materia seca */
    public const RANGOS_PV = [
        'Recepcion'     => [1.8, 2.5, 2.2],
        'Crecimiento'   => [2.3, 2.9, 2.6],
        'Finalizacion'  => [2.5, 3.2, 2.8],
        'Mantenimiento' => [1.8, 2.3, 2.0],
    ];

    protected $fillable = [
        'codigo',
        'nombre',
        'base_consumo',
        'pct_pv_min',
        'pct_pv_max',
        'pct_pv_sugerido',
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

    public function ingredientes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Ganaderia\RacionIngrediente::class, 'racion_id');
    }

    /**
     * % de materia seca de la dieta: promedio ponderado de sus ingredientes si todos lo tienen;
     * si no, el capturado en la dieta. Null si no hay dato.
     */
    public function materiaSecaCalculada(): ?float
    {
        $ings = $this->relationLoaded('ingredientes') ? $this->ingredientes : $this->ingredientes()->with('insumo')->get();
        if ($ings->isNotEmpty() && $ings->every(fn ($i) => $i->insumo && $i->insumo->materia_seca_pct !== null)) {
            return round($ings->sum(fn ($i) => (float) $i->porcentaje * (float) $i->insumo->materia_seca_pct) / max(0.0001, $ings->sum('porcentaje')), 2);
        }

        return $this->materia_seca_pct !== null && (float) $this->materia_seca_pct > 0 ? (float) $this->materia_seca_pct : null;
    }

    /** La receta suma 100 % (±0.5). */
    public function recetaCompleta(): bool
    {
        $suma = $this->relationLoaded('ingredientes') ? $this->ingredientes->sum('porcentaje') : $this->ingredientes()->sum('porcentaje');

        return abs((float) $suma - 100) <= 0.5;
    }
}
