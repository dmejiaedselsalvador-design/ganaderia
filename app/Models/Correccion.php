<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Corrección o anulación de un registro capturado (pesaje, servido, tratamiento, baja, venta o compra). */
class Correccion extends Model
{
    protected $table = 'correcciones';

    public const UPDATED_AT = null;

    public const TIPOS = [
        'pesaje' => 'Pesaje', 'servido' => 'Servido de alimento', 'tratamiento' => 'Tratamiento',
        'baja' => 'Baja', 'venta' => 'Venta', 'compra' => 'Datos de compra',
    ];

    protected $fillable = ['user_id', 'tipo', 'registro_id', 'accion', 'descripcion', 'motivo', 'antes', 'despues', 'ganado_id'];

    protected $casts = ['antes' => 'array', 'despues' => 'array', 'created_at' => 'datetime'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
