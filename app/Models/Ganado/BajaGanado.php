<?php

namespace App\Models\Ganado;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\TieneDocumentos;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $ganado_id
 * @property \Illuminate\Support\Carbon $fecha_muerte
 * @property string $causa_muerte
 * @property string|null $necropsia
 * @property float|null $ultimo_peso
 * @property string|null $observaciones
 * @property int|null $user_id
 * @property-read Ganado|null $ganado
 * @property-read User|null $usuario
 */
class BajaGanado extends Model
{
    use HasFactory, Auditable, TieneDocumentos;

    protected $table = 'bajas_ganado';

    /** Tipos de baja => estatus que toma el animal. */
    public const TIPOS = [
        'Muerte'     => 'Muerto',
        'Sacrificio' => 'Muerto',
        'Robo'       => 'Baja',
        'Extravio'   => 'Baja',
        'Descarte'   => 'Baja',
    ];

    public const CAUSAS_COMUNES = [
        'Neumonía / Complejo Respiratorio Bovino',
        'Timpanismo agudo (Meteorismo)',
        'Clostridiasis (Mancha / Carbón sintomático)',
        'Traumatismo / Fractura severa',
        'Picadura de víbora o insecto ponzoñoso',
        'Intoxicación por planta tóxica o químico',
        'Distocia / Complicación de parto',
        'Desconocida / Sin necropsia concluyente',
    ];

    protected $fillable = [
        'ganado_id',
        'tipo_baja',
        'valor_perdido_mxn',
        'valor_recuperado_mxn',
        'fecha_muerte',
        'causa_muerte',
        'necropsia',
        'ultimo_peso',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_muerte' => 'date',
        'ultimo_peso'  => 'decimal:2',
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
