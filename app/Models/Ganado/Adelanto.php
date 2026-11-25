<?php

namespace App\Models\Ganado;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $proveedor_id
 * @property string $concepto
 * @property float $dinero
 * @property \Illuminate\Support\Carbon $date
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read ProveedorGanado|null $proveedor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FacturaGanado> $facturas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AdelantoFactura> $aplicaciones
 */
class Adelanto extends Model
{
    use HasFactory, Auditable;

    protected $table = 'adelantos';

    public const STATUS_ENTREGADO = 'entregado';
    public const STATUS_LIQUIDADO = 'liquidado';
    public const STATUS_PARCIAL = 'parcial';

    protected $fillable = [
        'proveedor_id',
        'concepto',
        'dinero',
        'date',
        'status',
    ];

    protected $casts = [
        'dinero' => 'decimal:2',
        'date'   => 'date',
    ];

    /**
     * Mutador de conveniencia si se intenta asignar 'estado' en vez de 'status'.
     */
    public function setEstadoAttribute(?string $value): void
    {
        $this->attributes['status'] = ($value === 'disponible') ? self::STATUS_ENTREGADO : $value;
    }

    /**
     * Relación: Proveedor que recibió el adelanto.
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ProveedorGanado::class, 'proveedor_id', 'id');
    }

    /**
     * Relación: Facturas en las que se ha aplicado este adelanto.
     */
    public function facturas(): BelongsToMany
    {
        return $this->belongsToMany(FacturaGanado::class, 'adelanto_factura', 'adelanto_id', 'factura_id')
                    ->withPivot('montoAplicado')
                    ->withTimestamps();
    }

    /**
     * Relación: Registros de aplicación histórica.
     */
    public function aplicaciones(): HasMany
    {
        return $this->hasMany(AdelantoFactura::class, 'adelanto_id', 'id');
    }

    /**
     * Scope para adelantos pendientes de liquidación.
     */
    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENTREGADO);
    }

    /**
     * Scope para adelantos ya liquidados.
     */
    public function scopeLiquidado(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_LIQUIDADO);
    }
}
