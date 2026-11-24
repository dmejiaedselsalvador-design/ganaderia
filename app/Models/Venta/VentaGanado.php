<?php

namespace App\Models\Venta;

use App\Models\Ganado\Ganado;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $folio_venta
 * @property string $cliente_nombre
 * @property string|null $cliente_telefono
 * @property string|null $cliente_identificacion
 * @property string|null $destino
 * @property \Illuminate\Support\Carbon $fecha_venta
 * @property string $modalidad_pago
 * @property int $total_cabezas
 * @property float $peso_total_kg
 * @property float $subtotal
 * @property float $descuento
 * @property float $total_venta
 * @property string $estado
 * @property string|null $notas
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DetalleVentaGanado> $detalles
 * @property-read User|null $usuario
 */
class VentaGanado extends Model
{
    use HasFactory;

    protected $table = 'ventas_ganado';

    public const MODALIDAD_POR_KILO = 'por_kilo';
    public const MODALIDAD_PRECIO_FIJO = 'precio_fijo';

    public const ESTADO_COMPLETADA = 'completada';
    public const ESTADO_ANULADA = 'anulada';

    protected $fillable = [
        'folio_venta',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_identificacion',
        'destino',
        'fecha_venta',
        'modalidad_pago',
        'total_cabezas',
        'peso_total_kg',
        'subtotal',
        'descuento',
        'total_venta',
        'estado',
        'notas',
        'user_id',
    ];

    protected $casts = [
        'fecha_venta'   => 'date',
        'peso_total_kg' => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'descuento'     => 'decimal:2',
        'total_venta'   => 'decimal:2',
        'total_cabezas' => 'integer',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVentaGanado::class, 'venta_id', 'id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Generador automático de folios correlativos para ventas ganaderas.
     */
    public static function generarNuevoFolio(): string
    {
        $prefijo = 'VTA-' . date('Ym');
        $ultimo = self::where('folio_venta', 'LIKE', "{$prefijo}%")
                      ->orderBy('id', 'desc')
                      ->first();

        if (!$ultimo) {
            return "{$prefijo}-0001";
        }

        $partes = explode('-', $ultimo->folio_venta);
        $consecutivo = isset($partes[2]) ? ((int) $partes[2]) + 1 : 1;

        return sprintf('%s-%04d', $prefijo, $consecutivo);
    }
}
