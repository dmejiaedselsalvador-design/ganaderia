<?php

namespace App\Models\Ganaderia;

use App\Models\Engorda\RacionAlimento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RacionIngrediente extends Model
{
    protected $table = 'racion_ingredientes';

    protected $fillable = ['racion_id', 'insumo_id', 'porcentaje', 'cantidad', 'unidad_captura'];

    protected $casts = ['porcentaje' => 'decimal:3', 'cantidad' => 'decimal:3'];

    public function racion(): BelongsTo
    {
        return $this->belongsTo(RacionAlimento::class, 'racion_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class)->withTrashed();
    }
}
