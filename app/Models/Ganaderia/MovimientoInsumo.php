<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Engorda\CorralLote;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Renglón del kardex de un insumo. */
class MovimientoInsumo extends Model
{
    use Auditable;

    protected $table = 'movimientos_insumo';

    public const ENTRADA = 'entrada';
    public const SALIDA = 'salida';
    public const AJUSTE = 'ajuste';

    protected $fillable = [
        'insumo_id', 'tipo', 'fecha', 'cantidad', 'costo_unitario', 'existencia_resultante', 'lote',
        'fecha_caducidad', 'proveedor', 'referencia', 'origen_type', 'origen_id', 'corral_lote_id', 'notas', 'user_id',
    ];

    protected $casts = [
        'fecha'                 => 'date',
        'fecha_caducidad'       => 'date',
        'cantidad'              => 'decimal:3',
        'costo_unitario'        => 'decimal:4',
        'existencia_resultante' => 'decimal:3',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(Insumo::class)->withTrashed();
    }

    public function corral(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_lote_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function importe(): float
    {
        return round(abs((float) $this->cantidad) * (float) $this->costo_unitario, 2);
    }
}
