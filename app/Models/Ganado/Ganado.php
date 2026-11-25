<?php

namespace App\Models\Ganado;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\TieneDocumentos;
use App\Models\Calidad\EvaluacionCanal;
use App\Models\Engorda\CorralLote;
use App\Models\Sanidad\TratamientoSanitario;
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
    use HasFactory, Auditable, TieneDocumentos;

    protected $table = 'ganado';

    protected $primaryKey = 'GanadoID';

    public $incrementing = true;

    protected $keyType = 'int';

    public const STATUS_ACTIVO = 'Activo';
    public const STATUS_VENDIDO = 'Vendido';
    public const STATUS_MUERTO = 'Muerto';
    public const STATUS_EXPORTADO = 'Exportado';
    public const STATUS_BAJA = 'Baja'; // robo, extravío o descarte

    public const DENTICIONES = ['Dientes de leche', '2 dientes', '4 dientes', '6 dientes', 'Boca llena'];

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
        'arete_siniiga',
        'fierro',
        'color_senas',
        'procedencia',
        'upp_origen',
        'denticion',
        'peso_origen',
        'fecha_ingreso_corral',
        'costo_adicional_compra',
        'raza',
        'categoria',
        'lote_potrero',
        'corral_lote_id',
        'condicion_corporal',
        'dias_en_engorda',
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
        'fecha_ingreso_corral' => 'date',
        'peso_origen'      => 'decimal:2',
        'costo_adicional_compra' => 'decimal:2',
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
     * Relación: Corral o Lote de engorda técnico.
     */
    public function corralLote(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_lote_id', 'id');
    }

    /**
     * Relación: Bitácora de tratamientos sanitarios y medicamentos.
     */
    public function tratamientos(): HasMany
    {
        return $this->hasMany(TratamientoSanitario::class, 'ganado_id', 'GanadoID')
                    ->orderBy('fecha_aplicacion', 'desc');
    }

    /**
     * Relación: Evaluación de canal y calidad cárnica al sacrificio.
     */
    public function evaluacionCanal(): HasOne
    {
        return $this->hasOne(EvaluacionCanal::class, 'ganado_id', 'GanadoID');
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
        $dias = $this->fechaCompra ? max(1, (int) abs(now()->diffInDays($this->fechaCompra))) : 1;

        return round($this->ganancia_total / $dias, 3);
    }

    /**
     * Verifica si el animal está en periodo de retiro sanitario.
     */
    public function getEstaEnRetiroAttribute(): bool
    {
        return $this->tratamientos->contains(fn($t) => $t->esta_en_retiro);
    }

    /**
     * Días máximos restantes de retiro entre todos los tratamientos activos.
     */
    public function getDiasRestantesRetiroAttribute(): int
    {
        return (int) $this->tratamientos->max(fn($t) => $t->dias_restantes_retiro) ?? 0;
    }

    /**
     * Costo sanitario acumulado en medicamentos (MXN).
     */
    public function getCostoSanitarioTotalMxnAttribute(): float
    {
        return (float) $this->tratamientos->sum('costo_medicamento_mxn');
    }

    /**
     * Rendimiento estimado en canal caliente (%) basado en la raza y conformación.
     */
    public function getRendimientoCanalEstimadoPctAttribute(): float
    {
        $raza = strtolower($this->raza ?? '');
        if (str_contains($raza, 'angus') || str_contains($raza, 'charolais') || str_contains($raza, 'brangus') || str_contains($raza, 'beefmaster')) {
            return 61.50; // Cruzas cárnicas pesadas
        }
        if (str_contains($raza, 'cebu') || str_contains($raza, 'brahman')) {
            return 58.00;
        }
        return 59.50; // Estándar nacional mexicano
    }

    /**
     * Peso estimado de canal caliente (kg).
     */
    public function getPesoCanalEstimadoKgAttribute(): float
    {
        return round(((float) $this->ultimoPeso * ($this->rendimiento_canal_estimado_pct / 100)), 2);
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

    /** Historial de movimientos entre corrales. */
    public function movimientosCorral(): HasMany
    {
        return $this->hasMany(\App\Models\Ganaderia\MovimientoCorral::class, 'ganado_id', 'GanadoID')->orderByDesc('fecha')->orderByDesc('id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(\App\Models\Ganaderia\GanadoFoto::class, 'ganado_id', 'GanadoID')->latest();
    }

    public function eventosEnfermedad(): HasMany
    {
        return $this->hasMany(\App\Models\Ganaderia\EventoEnfermedad::class, 'ganado_id', 'GanadoID')->orderByDesc('fecha_inicio');
    }

    /** Costo de adquisición del animal: peso de ingreso x precio + prorrateo de flete/comisión/trámites. */
    public function costoAdquisicion(): float
    {
        return round((float) $this->pesoActual * (float) $this->precioCompra + (float) $this->costo_adicional_compra, 2);
    }

    /** Merma de viaje (destare) en kg y % contra el peso en origen. */
    public function mermaViaje(): ?array
    {
        if (! $this->peso_origen || (float) $this->peso_origen <= 0) {
            return null;
        }
        $kg = round((float) $this->peso_origen - (float) $this->pesoActual, 2);

        return ['kg' => $kg, 'pct' => round($kg / (float) $this->peso_origen * 100, 2)];
    }

    public function descripcionAuditoria(): string
    {
        return "Arete {$this->areteID}";
    }
}
