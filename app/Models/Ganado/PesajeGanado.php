<?php

namespace App\Models\Ganado;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ganado_id
 * @property float $peso
 * @property \Illuminate\Support\Carbon $fecha_pesaje
 * @property float|null $peso_anterior
 * @property float|null $ganancia_peso
 * @property int|null $dias_transcurridos
 * @property float|null $gdp
 * @property string|null $bascula
 * @property string|null $lote_corral
 * @property string|null $observaciones
 * @property int|null $user_id
 * @property-read Ganado|null $ganado
 * @property-read User|null $usuario
 */
class PesajeGanado extends Model
{
    use HasFactory;

    protected $table = 'pesajes_ganado';

    protected $fillable = [
        'ganado_id',
        'peso',
        'fecha_pesaje',
        'peso_anterior',
        'ganancia_peso',
        'dias_transcurridos',
        'gdp',
        'bascula',
        'lote_corral',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'peso'               => 'decimal:2',
        'peso_anterior'      => 'decimal:2',
        'ganancia_peso'      => 'decimal:2',
        'gdp'                => 'decimal:3',
        'fecha_pesaje'       => 'date',
        'dias_transcurridos' => 'integer',
    ];

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
