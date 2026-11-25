<?php

namespace App\Models\Ganaderia;

use App\Models\Ganado\Ganado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GanadoFoto extends Model
{
    protected $table = 'ganado_fotos';

    protected $fillable = ['ganado_id', 'ruta', 'descripcion', 'user_id'];

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }
}
