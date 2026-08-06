<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProveedorGanado extends Model
{
    //
    use HasFactory;
    protected $table = 'proveedorganado';
 protected $fillable = [
        'nombre',
        
        
    ];

}
