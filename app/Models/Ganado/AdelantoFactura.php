<?php
namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdelantoFactura extends Model
{
    protected $table = 'adelanto_factura';

    protected $fillable = [
        'adelanto_id',
        'factura_id',
        'montoAplicado',
    ];

    protected $casts = [
        'montoAplicado' => 'decimal:2',
    ];

    public function adelanto(): BelongsTo
    {
        return $this->belongsTo(Adelanto::class, 'adelanto_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaGanado::class, 'factura_id');
    }
}
