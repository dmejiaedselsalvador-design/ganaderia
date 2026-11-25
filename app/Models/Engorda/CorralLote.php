<?php

namespace App\Models\Engorda;

use App\Models\Concerns\Auditable;
use App\Models\Ganado\Ganado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo para la Gestión de Corrales y Lotes de Engorda (Feedlot Management)
 */
class CorralLote extends Model
{
    use HasFactory, Auditable;

    protected $table = 'corrales_lotes';

    protected $fillable = [
        'codigo',
        'nombre',
        'ubicacion_id',
        'meta_gdp',
        'meta_costo_kg',
        'meta_mortalidad_pct',
        'meta_conversion',
        'presupuesto_mxn',
        'fase_engorda',
        'capacidad_maxima',
        'peso_promedio_objetivo',
        'fecha_inicio_lote',
        'fecha_proyectada_salida',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'capacidad_maxima'        => 'integer',
        'peso_promedio_objetivo'  => 'decimal:2',
        'fecha_inicio_lote'       => 'date',
        'fecha_proyectada_salida' => 'date',
    ];

    /**
     * Ganado activo asignado a este corral.
     */
    public function ganados(): HasMany
    {
        return $this->hasMany(Ganado::class, 'corral_lote_id', 'id');
    }

    /**
     * Ganado activo únicamente.
     */
    public function ganadosActivos(): HasMany
    {
        return $this->hasMany(Ganado::class, 'corral_lote_id', 'id')
                    ->where('status', Ganado::STATUS_ACTIVO);
    }

    /**
     * Bitácora de consumo de alimento del corral.
     */
    public function consumos(): HasMany
    {
        return $this->hasMany(ConsumoAlimento::class, 'corral_lote_id', 'id')
                    ->orderBy('fecha', 'desc');
    }

    // ============================================
    // MÉTRICAS CALCULADAS DEL LOTE / CORRAL
    // ============================================

    public function getTotalCabezasAttribute(): int
    {
        return $this->ganadosActivos()->count();
    }

    public function getPesoPromedioActualAttribute(): float
    {
        return round((float) ($this->ganadosActivos()->avg('ultimoPeso') ?? 0), 2);
    }

    public function getPesoPromedioIngresoAttribute(): float
    {
        return round((float) ($this->ganadosActivos()->avg('pesoActual') ?? 0), 2);
    }

    public function getGdpPromedioLoteAttribute(): float
    {
        $ganados = $this->ganadosActivos()->get();
        if ($ganados->isEmpty()) {
            return 0.0;
        }

        $sumaGdp = $ganados->sum(fn($g) => $g->gdp_historica);
        return round($sumaGdp / $ganados->count(), 3);
    }

    public function getDiasPromedioEnEngordaAttribute(): int
    {
        if ($this->fecha_inicio_lote) {
            return max(1, (int) abs(now()->diffInDays($this->fecha_inicio_lote)));
        }

        $ganados = $this->ganadosActivos()->get();
        if ($ganados->isEmpty()) {
            return 0;
        }

        $promedioDias = $ganados->avg(fn($g) => $g->fechaCompra ? abs(now()->diffInDays($g->fechaCompra)) : 0);
        return (int) round($promedioDias ?? 0);
    }

    public function getInversionCompraTotalMxnAttribute(): float
    {
        return (float) ($this->ganadosActivos()->sum('precioGanadoTotal') ?? 0);
    }

    public function getCostoTotalAlimentoMxnAttribute(): float
    {
        return (float) ($this->consumos()->sum('costo_total_mxn') ?? 0);
    }

    public function getInversionTotalCorralMxnAttribute(): float
    {
        return round($this->inversion_compra_total_mxn + $this->costo_total_alimento_mxn, 2);
    }

    public function getKilosGanadosLoteAttribute(): float
    {
        return round((float) $this->ganadosActivos()->get()->sum(fn($g) => $g->ganancia_total), 2);
    }

    /**
     * Conversión Alimenticia del lote (kg alimento consumido / kg carne ganada)
     */
    public function getConversionAlimenticiaAttribute(): float
    {
        $kgGanados = $this->kilos_ganados_lote;
        $kgConsumidos = (float) $this->consumos()->sum('kilos_consumidos');

        if ($kgGanados <= 0 || $kgConsumidos <= 0) {
            return 0.0;
        }

        return round($kgConsumidos / $kgGanados, 2);
    }

    /**
     * Ocupación porcentual del corral.
     */
    public function getPorcentajeOcupacionAttribute(): float
    {
        if ($this->capacidad_maxima <= 0) return 0.0;
        return round(($this->total_cabezas / $this->capacidad_maxima) * 100, 1);
    }

    public function ubicacion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Ganaderia\Ubicacion::class);
    }

    public function descripcionAuditoria(): string
    {
        return "{$this->codigo} {$this->nombre}";
    }

    /** Dieta vigente del corral (la asignación sin fecha de término). */
    public function dietaVigente(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Ganaderia\AsignacionDieta::class, 'corral_lote_id')->whereNull('hasta')->latestOfMany('desde');
    }

    public function asignacionesDieta(): HasMany
    {
        return $this->hasMany(\App\Models\Ganaderia\AsignacionDieta::class, 'corral_lote_id')->orderByDesc('desde')->orderByDesc('id');
    }
}
