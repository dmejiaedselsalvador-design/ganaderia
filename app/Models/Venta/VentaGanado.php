<?php

namespace App\Models\Venta;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\TieneDocumentos;
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
    use HasFactory, Auditable, TieneDocumentos;

    protected $table = 'ventas_ganado';

    public const MODALIDAD_POR_KILO = 'por_kilo';
    public const MODALIDAD_PRECIO_FIJO = 'precio_fijo';

    public const ESTADO_COMPLETADA = 'completada';
    public const ESTADO_ANULADA = 'anulada';

    protected $fillable = [
        'folio_venta',
        'cliente_id',
        'condicion_pago',
        'forma_pago',
        'fecha_vencimiento',
        'saldo_pendiente',
        'folio_cfdi',
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
        'fecha_vencimiento' => 'date',
        'saldo_pendiente' => 'decimal:2',
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

    public const CONTADO = 'contado';
    public const CREDITO = 'credito';

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Ganaderia\Cliente::class)->withTrashed();
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(\App\Models\Ganaderia\AbonoVenta::class, 'venta_id')->orderByDesc('fecha')->orderByDesc('id');
    }

    /** Recalcula el saldo pendiente a partir de los abonos vigentes. */
    public function recalcularSaldo(): void
    {
        $pagado = (float) $this->abonos()->where('estado', 'vigente')->sum('monto');
        $this->forceFill(['saldo_pendiente' => max(0, round((float) $this->total_venta - $pagado, 2))])->save();
    }

    public function totalPagado(): float
    {
        return round((float) $this->total_venta - (float) $this->saldo_pendiente, 2);
    }

    public function vencida(): bool
    {
        return (float) $this->saldo_pendiente > 0 && $this->fecha_vencimiento && $this->fecha_vencimiento->isPast() && ! $this->fecha_vencimiento->isToday();
    }

    public function descripcionAuditoria(): string
    {
        return "Venta {$this->folio_venta}";
    }
}
