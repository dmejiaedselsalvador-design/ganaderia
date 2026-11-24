<?php

namespace App\Models\Venta;

use App\Models\Ganado\Ganado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $venta_id
 * @property int $ganado_id
 * @property string $arete_snapshot
 * @property string $categoria_snapshot
 * @property string|null $raza_snapshot
 * @property float $peso_venta
 * @property float $precio_unitario
 * @property float $subtotal
 * @property-read VentaGanado|null $venta
 * @property-read Ganado|null $ganado
 */
class DetalleVentaGanado extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas_ganado';

    protected $fillable = [
        'venta_id',
        'ganado_id',
        'arete_snapshot',
        'categoria_snapshot',
        'raza_snapshot',
        'peso_venta',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'peso_venta'      => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(VentaGanado::class, 'venta_id', 'id');
    }

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }
}
