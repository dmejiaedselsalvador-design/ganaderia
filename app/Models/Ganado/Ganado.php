<?php

namespace App\Models\Ganado;

use App\Models\Venta\DetalleVentaGanado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property int $GanadoID
 * @property int|null $facturaID
 * @property string $areteID
 * @property string|null $raza
 * @property string $categoria
 * @property string|null $lote_potrero
 * @property string $sexo
 * @property float|null $pesoActual
 * @property float $ultimoPeso
 * @property float|null $peso_objetivo
 * @property float|null $precioCompra
 * @property float|null $precioGanadoTotal
 * @property \Illuminate\Support\Carbon|null $fechaCompra
 * @property \Illuminate\Support\Carbon|null $fecha_nacimiento
 * @property string|null $madre_arete
 * @property string|null $padre_arete
 * @property string $status
 * @property string|null $notas
 * @property string|null $foto
 * @property-read FacturaGanado|null $factura
 * @property-read ProveedorGanado|null $proveedor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PesajeGanado> $pesajes
 * @property-read PesajeGanado|null $ultimoPesaje
 * @property-read BajaGanado|null $baja
 * @property-read DetalleVentaGanado|null $detalleVenta
 */
class Ganado extends Model
{
    use HasFactory;

    protected $table = 'ganado';

    protected $primaryKey = 'GanadoID';

    public $incrementing = true;

    protected $keyType = 'int';

    public const STATUS_ACTIVO = 'Activo';
    public const STATUS_VENDIDO = 'Vendido';
    public const STATUS_MUERTO = 'Muerto';
    public const STATUS_EXPORTADO = 'Exportado';

    public const CATEGORIAS_MACHOS = ['Becerro', 'Torete', 'Toro'];
    public const CATEGORIAS_HEMBRAS = ['Becerra', 'Vaquilla', 'Vaca'];

    /**
     * Umbrales de peso objetivo por defecto según la categoría (en kg).
     */
    public const UMBRALES_VENTA_CATEGORIA = [
        'Toro'     => 420.00,
        'Torete'   => 380.00,
        'Vaca'     => 400.00,
        'Vaquilla' => 350.00,
        'Becerro'  => 220.00,
        'Becerra'  => 210.00,
    ];

    protected $fillable = [
        'facturaID',
        'areteID',
        'raza',
        'categoria',
        'lote_potrero',
        'sexo',
        'pesoActual',
        'ultimoPeso',
        'peso_objetivo',
        'precioCompra',
        'fechaCompra',
        'fecha_nacimiento',
        'madre_arete',
        'padre_arete',
        'status',
        'notas',
        'foto',
    ];

    protected $casts = [
        'pesoActual'       => 'decimal:2',
        'ultimoPeso'       => 'decimal:2',
        'peso_objetivo'    => 'decimal:2',
        'precioCompra'     => 'decimal:2',
        'fechaCompra'      => 'date',
        'fecha_nacimiento' => 'date',
    ];

    /**
     * Relación: Factura o guía de compra.
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaGanado::class, 'facturaID', 'id');
    }

    /**
     * Relación: Proveedor a través de la factura.
     */
    public function proveedor(): HasOneThrough
    {
        return $this->hasOneThrough(
            ProveedorGanado::class,
            FacturaGanado::class,
            'id',          // Llave en facturaGanado
            'id',          // Llave en proveedorGanado
            'facturaID',   // Llave local en ganado
            'proveedorID'  // Llave local en facturaGanado
        );
    }

    /**
     * Relación: Historial completo de pesajes ordenados de más reciente a más antiguo.
     */
    public function pesajes(): HasMany
    {
        return $this->hasMany(PesajeGanado::class, 'ganado_id', 'GanadoID')
                    ->orderBy('fecha_pesaje', 'desc')
                    ->orderBy('id', 'desc');
    }

    /**
     * Relación: Último pesaje registrado en báscula.
     */
    public function ultimoPesaje(): HasOne
    {
        return $this->hasOne(PesajeGanado::class, 'ganado_id', 'GanadoID')
                    ->latestOfMany('fecha_pesaje');
    }

    /**
     * Relación: Registro de baja o defunción si el animal murió.
     */
    public function baja(): HasOne
    {
        return $this->hasOne(BajaGanado::class, 'ganado_id', 'GanadoID');
    }

    /**
     * Relación: Registro de venta si el animal ya fue comercializado.
     */
    public function detalleVenta(): HasOne
    {
        return $this->hasOne(DetalleVentaGanado::class, 'ganado_id', 'GanadoID');
    }

    /**
     * Accesor para proveedor con fallback seguro.
     */
    public function getProveedorAttribute(): ?ProveedorGanado
    {
        if ($this->relationLoaded('proveedor')) {
            return $this->getRelation('proveedor');
        }

        return $this->factura?->proveedor;
    }

    /**
     * Mutador automático para asignar sexo según la categoría seleccionada.
     */
    public function setCategoriaAttribute($value): void
    {
        $this->attributes['categoria'] = $value;
        $this->attributes['sexo'] = in_array($value, self::CATEGORIAS_MACHOS, true) ? 'Macho' : 'Hembra';
    }

    /**
     * Accesor para asegurar cálculo del precio total incluso en entornos sin columna generada.
     */
    public function getPrecioGanadoTotalAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $peso = (float) ($this->ultimoPeso ?? $this->pesoActual ?? 0);
        $precio = (float) ($this->precioCompra ?? 0);

        return round($peso * $precio, 2);
    }

    /**
     * Accesor: Peso objetivo para venta (personalizado o por categoría).
     */
    public function getPesoObjetivoCalculadoAttribute(): float
    {
        if (!empty($this->peso_objetivo) && (float) $this->peso_objetivo > 0) {
            return (float) $this->peso_objetivo;
        }

        return (float) (self::UMBRALES_VENTA_CATEGORIA[$this->categoria] ?? 400.00);
    }

    /**
     * Regla / Alerta: Determina si el animal ya alcanzó o superó el peso de venta.
     */
    public function getEstaListoVentaAttribute(): bool
    {
        if ($this->status !== self::STATUS_ACTIVO) {
            return false;
        }

        return (float) $this->ultimoPeso >= $this->peso_objetivo_calculado;
    }

    /**
     * Ganancia total de peso desde el ingreso al corral (kg).
     */
    public function getGananciaTotalAttribute(): float
    {
        $inicial = (float) ($this->pesoActual ?? $this->ultimoPeso ?? 0);
        $actual = (float) ($this->ultimoPeso ?? 0);

        return round($actual - $inicial, 2);
    }

    /**
     * Ganancia Diaria de Peso promedio (kg/día) desde el ingreso.
     */
    public function getGdpHistoricaAttribute(): float
    {
        $dias = $this->fechaCompra ? max(1, now()->diffInDays($this->fechaCompra)) : 1;

        return round($this->ganancia_total / $dias, 3);
    }

    /**
     * Verifica si el animal está en condición operativa (activo, vivo y en corral).
     */
    public function puedeOperar(): bool
    {
        return $this->status === self::STATUS_ACTIVO;
    }

    public function isActivo(): bool
    {
        return $this->status === self::STATUS_ACTIVO;
    }

    public function isMuerto(): bool
    {
        return $this->status === self::STATUS_MUERTO;
    }

    public function isVendido(): bool
    {
        return $this->status === self::STATUS_VENDIDO;
    }

    /**
     * Scopes.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVO);
    }

    public function scopeListoParaVenta(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVO)
                     ->where(function ($q) {
                         foreach (self::UMBRALES_VENTA_CATEGORIA as $categoria => $umbral) {
                             $q->orWhere(function ($sub) use ($categoria, $umbral) {
                                 $sub->where('categoria', $categoria)
                                     ->whereRaw("COALESCE(peso_objetivo, {$umbral}) <= ultimoPeso");
                             });
                         }
                     });
    }

    public function scopeMuerto(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_MUERTO);
    }

    public function scopeVendido(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VENDIDO);
    }
}
