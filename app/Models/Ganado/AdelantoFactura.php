<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $adelanto_id
 * @property int $factura_id
 * @property float $montoAplicado
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Adelanto|null $adelanto
 * @property-read FacturaGanado|null $factura
 */
class AdelantoFactura extends Model
{
    use HasFactory;

    protected $table = 'adelanto_factura';

    protected $fillable = [
        'adelanto_id',
        'factura_id',
        'montoAplicado',
    ];

    protected $casts = [
        'montoAplicado' => 'decimal:2',
    ];

    /**
     * Relación: Adelanto origen.
     */
    public function adelanto(): BelongsTo
    {
        return $this->belongsTo(Adelanto::class, 'adelanto_id', 'id');
    }

    /**
     * Relación: Factura o guía donde se amortizó.
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaGanado::class, 'factura_id', 'id');
    }
}
