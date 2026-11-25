<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Engorda\CorralLote;
use App\Models\Engorda\RacionAlimento;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Dieta asignada a un corral durante un periodo (historial). */
class AsignacionDieta extends Model
{
    use Auditable;

    protected $table = 'asignaciones_dieta';

    protected $fillable = ['corral_lote_id', 'racion_id', 'pct_pv', 'desde', 'hasta', 'notas', 'user_id'];

    protected $casts = ['desde' => 'date', 'hasta' => 'date', 'pct_pv' => 'decimal:2'];

    public function corral(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_lote_id');
    }

    public function racion(): BelongsTo
    {
        return $this->belongsTo(RacionAlimento::class, 'racion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeVigentes(Builder $q): Builder
    {
        return $q->whereNull('hasta');
    }

    public function descripcionAuditoria(): string
    {
        return "Dieta {$this->racion_id} en corral {$this->corral_lote_id}";
    }
}
