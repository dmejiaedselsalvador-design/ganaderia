<?php

namespace App\Models\Ganado;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $nombreProveedor
 * @property string $estado
 * @property string|null $nombreContacto
 * @property string|null $telefono
 * @property string|null $lugar
 * @property string|null $ubicacion
 * @property string|null $razon_social
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Adelanto> $adelantos
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FacturaGanado> $facturasGanado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ganado> $ganadoDirecto
 */
class ProveedorGanado extends Model
{
    use HasFactory, Auditable;

    protected $table = 'proveedorGanado';

    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_INACTIVO = 'inactivo';

    protected $fillable = [
        'nombreProveedor',
        'nombreContacto',
        'telefono',
        'lugar',
        'razon_social',
        'ubicacion',
        'estado',
    ];

    /**
     * Mutador de conveniencia para tolerar la errata tipográfica histórica 'nombreProoveedor'.
     */
    public function setNombreProoveedorAttribute(?string $value): void
    {
        $this->attributes['nombreProveedor'] = $value;
    }

    /**
     * Relación: Adelantos y préstamos otorgados al proveedor.
     */
    public function adelantos(): HasMany
    {
        return $this->hasMany(Adelanto::class, 'proveedor_id', 'id');
    }

    /**
     * Relación: Facturas o guías emitidas por el proveedor.
     */
    public function facturasGanado(): HasMany
    {
        return $this->hasMany(FacturaGanado::class, 'proveedorID', 'id');
    }

    /**
     * Relación: Todo el ganado comprado a este proveedor a través de sus facturas.
     */
    public function ganadoDirecto(): HasManyThrough
    {
        return $this->hasManyThrough(
            Ganado::class,
            FacturaGanado::class,
            'proveedorID', // Llave foránea en facturaGanado
            'facturaID',   // Llave foránea en ganado
            'id',          // Llave local en proveedorGanado
            'id'           // Llave local en facturaGanado
        );
    }

    /**
     * Scope para proveedores activos.
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    /**
     * Determina si el proveedor cuenta con adelantos activos o saldo deudor pendiente.
     */
    public function tieneDeudaPendiente(): bool
    {
        return $this->adelantos()
            ->where('status', Adelanto::STATUS_ENTREGADO)
            ->where('dinero', '>', 0)
            ->exists();
    }
}
