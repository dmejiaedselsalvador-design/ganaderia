<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


class ProveedorGanado extends Model
{
    //
    use HasFactory;
    protected $table = 'proveedorGanado';
 protected $fillable = [
        'nombreProveedor',
        'nombreContacto',
        'telefono',
        'lugar',
        'razon_social',
        'ubicacion',
        'estado',


    ];

public function adelantos()
{
    // Asegúrate de que el segundo parámetro sea el nombre de la llave foránea en tu tabla adelantos
    return $this->hasMany(Adelanto::class, 'proveedor_id');
}

public function facturasGanado(): HasMany
    {
        return $this->hasMany(FacturaGanado::class, 'proveedorID');
    }

    public function ganado(): HasMany
    {
        return $this->hasMany(Ganado::class, 'proveedorID');
    }

    public function ganadoDirecto(): HasManyThrough
{
    // 3er parámetro: llave foránea en la tabla intermedia (facturaGanado)
    // 4to parámetro: llave foránea en la tabla final (ganado)
    return $this->hasManyThrough(
        Ganado::class,
        FacturaGanado::class,
        'proveedorID', // Foreign key en facturaGanado
        'facturaID',   // Foreign key en ganado
        'id',          // Local key en proveedorGanado
        'id'           // Local key en facturaGanado
    );
}

}
