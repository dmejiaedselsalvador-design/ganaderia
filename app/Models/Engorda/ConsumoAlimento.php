<?php

namespace App\Models\Engorda;

use App\Models\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumoAlimento extends Model
{
    use HasFactory, Auditable;

    protected $table = 'consumos_alimento';

    protected $fillable = [
        'corral_lote_id',
        'racion_id',
        'fecha',
        'kilos_servidos',
        'kilos_rechazo',
        'kilos_consumidos',
        'costo_total_mxn',
        'cabezas_contadas',
        'consumo_promedio_cabeza_kg',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha'                      => 'date',
        'kilos_servidos'             => 'decimal:2',
        'kilos_rechazo'              => 'decimal:2',
        'kilos_consumidos'           => 'decimal:2',
        'costo_total_mxn'            => 'decimal:2',
        'cabezas_contadas'           => 'integer',
        'consumo_promedio_cabeza_kg' => 'decimal:2',
    ];

    public function corralLote(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_lote_id', 'id');
    }

    public function racion(): BelongsTo
    {
        return $this->belongsTo(RacionAlimento::class, 'racion_id', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
