<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Alimento, medicamento u otro insumo con existencia y costo promedio. */
class Insumo extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'insumos';

    public const TIPOS = ['alimento' => 'Alimento / Ingrediente', 'medicamento' => 'Medicamento / Biológico', 'otro' => 'Otro insumo'];
    public const UNIDADES = ['kg', 'ton', 'lb', 'g', 'l', 'ml', 'dosis', 'pieza', 'saco', 'frasco'];

    /** Unidades de peso con su equivalencia fija en kg */
    public const KG_POR_UNIDAD_PESO = ['kg' => 1.0, 'g' => 0.001, 'lb' => 0.45359237, 'ton' => 1000.0];

    protected $fillable = [
        'codigo', 'nombre', 'tipo', 'unidad', 'materia_seca_pct', 'factor_kg', 'controla_existencia', 'existencia', 'costo_promedio', 'stock_minimo',
        'dias_retiro', 'presentacion', 'activo', 'notas',
    ];

    protected $casts = [
        'existencia'     => 'decimal:3',
        'costo_promedio' => 'decimal:4',
        'stock_minimo'   => 'decimal:3',
        'materia_seca_pct' => 'decimal:2',
        'factor_kg'      => 'decimal:3',
        'activo'         => 'boolean',
        'controla_existencia' => 'boolean',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInsumo::class)->orderByDesc('fecha')->orderByDesc('id');
    }

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    public function scopeBajoMinimo(Builder $q): Builder
    {
        return $q->where('activo', true)->where('stock_minimo', '>', 0)->whereColumn('existencia', '<=', 'stock_minimo');
    }

    public function estaBajoMinimo(): bool
    {
        return (float) $this->stock_minimo > 0 && (float) $this->existencia <= (float) $this->stock_minimo;
    }

    /** Kilos que contiene una unidad del insumo (kg = 1, ton = 1000, saco = lo capturado). */
    public function kgPorUnidad(): float
    {
        return self::KG_POR_UNIDAD_PESO[$this->unidad] ?? max(0.000001, (float) ($this->factor_kg ?: 1));
    }

    /** kg que pesa un litro de este insumo (agua y melaza ~1.0–1.4). Si no se maneja en litros, 1. */
    public function kgPorLitro(): float
    {
        return match ($this->unidad) {
            'l'     => (float) ($this->factor_kg ?: 1),
            'ml'    => (float) ($this->factor_kg ?: 0.001) * 1000,
            default => 1.0,
        };
    }

    /**
     * Convierte una cantidad capturada en la receta a kg.
     * kg/g/lb/ton: equivalencia fija · l/ml: con el peso por litro del insumo · su propia unidad (saco, pieza…): kg por unidad.
     */
    public function kgDe(float $cantidad, string $unidad): float
    {
        return $cantidad * match (true) {
            isset(self::KG_POR_UNIDAD_PESO[$unidad]) => self::KG_POR_UNIDAD_PESO[$unidad],
            $unidad === 'l'  => $this->kgPorLitro(),
            $unidad === 'ml' => $this->kgPorLitro() / 1000,
            default          => $this->kgPorUnidad(),
        };
    }

    /** Unidades en que se puede capturar este insumo dentro de una receta. */
    public function unidadesReceta(): array
    {
        return array_values(array_unique(array_merge(['kg', 'g', 'lb', 'ton', 'l', 'ml'], [$this->unidad])));
    }

    public function existenciaKg(): float
    {
        return (float) $this->existencia * $this->kgPorUnidad();
    }

    /** Costo por kg (el costo promedio es por unidad). */
    public function costoKg(): float
    {
        return (float) $this->costo_promedio / $this->kgPorUnidad();
    }

    public function valorInventario(): float
    {
        return round((float) $this->existencia * (float) $this->costo_promedio, 2);
    }

    /** Lotes con existencia próxima a caducar (entradas con caducidad dentro de N días). */
    public static function lotesPorCaducar(int $dias = 30)
    {
        return MovimientoInsumo::with('insumo')
            ->where('tipo', MovimientoInsumo::ENTRADA)
            ->whereNotNull('fecha_caducidad')
            ->whereDate('fecha_caducidad', '<=', now()->addDays($dias))
            ->whereHas('insumo', fn ($q) => $q->where('existencia', '>', 0)->where('activo', true))
            ->orderBy('fecha_caducidad')
            ->get();
    }

    public function descripcionAuditoria(): string
    {
        return "{$this->codigo} {$this->nombre}";
    }
}
