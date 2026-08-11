<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProveedorGanado extends Model
{
    //
    use HasFactory;
    protected $table = 'proveedorGanado';
 protected $fillable = [
        'nombreProoveedor',
        'nombreContacto',
        'telefono',
        'lugar',
        'razon_social',
        'ubicacion',


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

}
