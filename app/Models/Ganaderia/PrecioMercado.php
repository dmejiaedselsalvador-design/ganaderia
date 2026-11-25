<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** Precio de mercado del ganado en pie (MXN/kg) por categoría y fecha. */
class PrecioMercado extends Model
{
    use Auditable;

    protected $table = 'precios_mercado';

    public const GENERAL = 'General';

    protected $fillable = ['fecha', 'categoria', 'precio_kg', 'fuente', 'notas', 'user_id'];

    protected $casts = ['fecha' => 'date', 'precio_kg' => 'decimal:2'];

    /**
     * Último precio vigente por categoría: ['Torete' => 62.5, 'General' => 60, ...].
     */
    public static function vigentes(): Collection
    {
        return static::query()
            ->whereIn('id', static::query()->selectRaw('MAX(id)')->groupBy('categoria'))
            ->get()
            ->mapWithKeys(fn ($p) => [$p->categoria => (float) $p->precio_kg]);
    }

    /** Precio para una categoría (o el General si no hay uno específico). */
    public static function precioPara(?string $categoria, ?Collection $vigentes = null): ?float
    {
        $vigentes ??= static::vigentes();

        return $vigentes[$categoria] ?? $vigentes[self::GENERAL] ?? null;
    }

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function descripcionAuditoria(): string
    {
        return "{$this->categoria} {$this->fecha?->format('d/m/Y')}";
    }
}
