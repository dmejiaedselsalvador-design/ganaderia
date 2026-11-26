<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Ganado\Ganado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/** Peso objetivo de venta por categoría (configurable por el usuario). */
class PesoObjetivo extends Model
{
    use Auditable;

    protected $table = 'pesos_objetivo';

    protected $fillable = ['categoria', 'peso_kg', 'user_id'];

    protected $casts = ['peso_kg' => 'decimal:2'];

    private static ?array $cache = null;

    /** categoría => kg (lo configurado, o el valor de fábrica si no hay). */
    public static function mapa(): array
    {
        if (self::$cache === null) {
            try {
                $config = Schema::hasTable('pesos_objetivo') ? static::query()->pluck('peso_kg', 'categoria')->map(fn ($v) => (float) $v)->all() : [];
            } catch (\Throwable) {
                return Ganado::UMBRALES_VENTA_CATEGORIA; // sin base de datos (p. ej. pruebas unitarias): valores de fábrica
            }
            self::$cache = array_merge(Ganado::UMBRALES_VENTA_CATEGORIA, $config);
        }

        return self::$cache;
    }

    public static function limpiarCache(): void
    {
        self::$cache = null;
    }

    public function descripcionAuditoria(): string
    {
        return "Peso objetivo {$this->categoria}: {$this->peso_kg} kg";
    }
}
