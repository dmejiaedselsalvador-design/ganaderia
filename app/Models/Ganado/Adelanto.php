<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Ganado\ProveedorGanado; // Importante
use App\Models\Ganado\AdelantoFactura;
use App\Models\Ganado\FacturaGanado;

class Adelanto extends Model
{
    //
    protected $table = 'adelantos';

    protected $fillable = [
        'proveedor_id',
        'concepto',
        'dinero',
        'montoDisponible',
        'date',
        'status',
    ];

    protected $casts = [
        'dinero' => 'decimal:2',
        'montoDisponible' => 'decimal:2',
        'date' => 'date',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ProveedorGanado::class, 'proveedor_id');
    }

    public function aplicaciones(): HasMany
    {
        return $this->hasMany(AdelantoFactura::class, 'adelanto_id');
    }


}
