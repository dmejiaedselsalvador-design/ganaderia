<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacturaGanado extends Model
{
    use HasFactory;

    // Nombre explícito de la tabla en tu base de datos
    protected $table = 'facturaGanado';

    // Campos permitidos para asignación masiva (Mass Assignment)
    protected $fillable = [
        'proveedorID',
        'fechaFactura',
        'numeroFactura',
        'montoTotal',
        'estado',
        'notas',
    ];

    // Conversión automática de tipos de datos
    protected $casts = [
        'fechaFactura' => 'date',
        'montoTotal' => 'decimal:2',
    ];

    /**
     * Relación: Una factura pertenece a un proveedor.
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ProveedorGanado::class, 'proveedorID');
    }

    /**
     * Relación: Una factura agrupa a muchos animales (ganado).
     */
    public function animales(): HasMany
    {
        return $this->hasMany(Ganado::class, 'facturaID');
    }
}
