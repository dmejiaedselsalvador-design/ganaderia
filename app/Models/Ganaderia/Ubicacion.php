<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Engorda\CorralLote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Rancho, engorda o predio donde están los corrales. */
class Ubicacion extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'ubicaciones';

    protected $fillable = ['nombre', 'clave_upp', 'municipio', 'estado', 'responsable', 'direccion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function corrales(): HasMany
    {
        return $this->hasMany(CorralLote::class, 'ubicacion_id');
    }

    public function descripcionAuditoria(): string
    {
        return $this->nombre;
    }
}
