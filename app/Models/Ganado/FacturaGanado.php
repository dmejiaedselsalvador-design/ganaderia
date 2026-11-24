<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $proveedorID
 * @property \Illuminate\Support\Carbon|null $fechaFactura
 * @property string|null $numeroFactura
 * @property float|null $montoTotal
 * @property string $estado
 * @property string|null $notas
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read ProveedorGanado|null $proveedor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ganado> $animales
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Adelanto> $adelantos
 */
class FacturaGanado extends Model
{
    use HasFactory;

    protected $table = 'facturaGanado';

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_LIQUIDADO = 'liquidado';
    public const ESTADO_PARCIAL = 'parcial';
    public const ESTADO_ANULADA = 'anulada';

    protected $fillable = [
        'proveedorID',
        'fechaFactura',
        'numeroFactura',
        'montoTotal',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fechaFactura' => 'date',
        'montoTotal'   => 'decimal:2',
    ];

    /**
     * Relación: Una factura pertenece a un proveedor.
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ProveedorGanado::class, 'proveedorID', 'id');
    }

    /**
     * Alias para compatibilidad con vistas existentes.
     */
    public function proveedorData(): BelongsTo
    {
        return $this->proveedor();
    }

    /**
     * Relación: Una factura agrupa a muchos animales (ganado).
     */
    public function animales(): HasMany
    {
        return $this->hasMany(Ganado::class, 'facturaID', 'id');
    }

    /**
     * Relación: Adelantos vinculados o amortizados en esta factura.
     */
    public function adelantos(): BelongsToMany
    {
        return $this->belongsToMany(Adelanto::class, 'adelanto_factura', 'factura_id', 'adelanto_id')
                    ->withPivot('montoAplicado')
                    ->withTimestamps();
    }

    /**
     * Scope para facturas con liquidación pendiente.
     */
    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    /**
     * Scope para facturas liquidadas.
     */
    public function scopeLiquidada(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_LIQUIDADO);
    }

    /**
     * Accesor: Monto total calculado a partir de los animales del lote.
     */
    public function getMontoTotalCalculadoAttribute(): float
    {
        return (float) $this->animales->sum(function ($animal) {
            return (float) ($animal->precioGanadoTotal ?? ($animal->ultimoPeso * $animal->precioCompra));
        });
    }

    /**
     * Accesor: Total de adelantos amortizados.
     */
    public function getTotalAdelantosAplicadosAttribute(): float
    {
        return (float) $this->adelantos->sum(function ($adelanto) {
            return (float) ($adelanto->pivot->montoAplicado ?? 0);
        });
    }

    /**
     * Accesor: Saldo pendiente tras deducir adelantos aplicados.
     */
    public function getSaldoPendienteAttribute(): float
    {
        return max(0.0, $this->monto_total_calculado - $this->total_adelantos_aplicados);
    }
}
