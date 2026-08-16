<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
   public function proveedor()
{
    return $this->belongsTo(ProveedorGanado::class, 'proveedorID', 'id');
}

      public function proveedorData()
    {
        return $this->belongsTo(ProveedorGanado::class, 'proveedorID', 'id');
    }





    /**
     * Relación: Una factura agrupa a muchos animales (ganado).
     */
    public function animales(): HasMany
    {
        return $this->hasMany(Ganado::class, 'facturaID');
    }
    public function adelantos(): BelongsToMany
    {
        return $this->belongsToMany(Adelanto::class, 'adelanto_factura', 'factura_id', 'adelanto_id')
                    ->withPivot('montoAplicado')
                    ->withTimestamps();
    }

    // ACCESOR: Monto Total calculado del ganado
    public function getMontoTotalAttribute()
    {
        return $this->animales->sum('precioGanadoTotal');
    }

    // ACCESOR: Total de adelantos aplicados
    public function getTotalAdelantosAplicadosAttribute()
    {
        return $this->adelantos->sum('pivot.montoAplicado');
    }

    // ACCESOR: Saldo pendiente real
    public function getSaldoPendienteAttribute()
    {
        return max(0, $this->montoTotal - $this->total_adelantos_aplicados);
    }
}
