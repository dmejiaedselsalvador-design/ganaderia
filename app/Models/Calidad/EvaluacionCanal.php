<?php

namespace App\Models\Calidad;

use App\Models\Ganado\Ganado;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Calidad de Carne y Tipificación de Canales Bovinas
 * Basado en la metodología de la Dra. María Salud Rubio Lozano (UNAM / Engormix)
 * y la Norma Mexicana NMX-FF-078-SCFI.
 */
class EvaluacionCanal extends Model
{
    use HasFactory;

    protected $table = 'evaluaciones_canal';

    protected $fillable = [
        'ganado_id',
        'arete_snapshot',
        'folio_rastro_canal',
        'fecha_faena',
        'planta_o_rastro',
        'peso_vivo_bascula_kg',
        'peso_canal_caliente_kg',
        'rendimiento_canal_caliente_pct',
        'peso_canal_fria_kg',
        'merma_enfriamiento_pct',
        'rendimiento_canal_fria_pct',
        'marmoleo',
        'espesor_grasa_dorsal_mm',
        'area_ojo_costilla_cm2',
        'color_carne',
        'color_grasa',
        'ph_24h',
        'clasificacion_mexicana',
        'precio_canal_por_kg_mxn',
        'valor_total_canal_mxn',
        'destino_comercial',
        'observaciones_tecnicas',
        'user_id',
    ];

    protected $casts = [
        'fecha_faena'                    => 'date',
        'peso_vivo_bascula_kg'           => 'decimal:2',
        'peso_canal_caliente_kg'         => 'decimal:2',
        'rendimiento_canal_caliente_pct' => 'decimal:2',
        'peso_canal_fria_kg'             => 'decimal:2',
        'merma_enfriamiento_pct'         => 'decimal:2',
        'rendimiento_canal_fria_pct'     => 'decimal:2',
        'espesor_grasa_dorsal_mm'        => 'decimal:2',
        'area_ojo_costilla_cm2'          => 'decimal:2',
        'ph_24h'                         => 'decimal:2',
        'precio_canal_por_kg_mxn'        => 'decimal:2',
        'valor_total_canal_mxn'          => 'decimal:2',
    ];

    public function ganado(): BelongsTo
    {
        return $this->belongsTo(Ganado::class, 'ganado_id', 'GanadoID');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * pH ideal (5.40 - 5.70): Oximioglobina roja brillante, excelente vida de anaquel y terneza.
     */
    public function getEsPhOptimoAttribute(): bool
    {
        $ph = (float) $this->ph_24h;
        return $ph >= 5.40 && $ph <= 5.75;
    }

    /**
     * pH > 5.80: Corte Oscuro / DFD (Dark, Firm, Dry) debido a estrés pre-faena o ayuno prolongado.
     */
    public function getEsCorteOscuroDfdAttribute(): bool
    {
        return (float) $this->ph_24h > 5.80;
    }

    /**
     * Estado zootécnico del pH según los parámetros de Dra. Rubio.
     */
    public function getDiagnosticoPhAttribute(): array
    {
        $ph = (float) $this->ph_24h;

        if ($ph < 5.40) {
            return [
                'estado'      => 'pH Bajo (Riesgo PSE leve)',
                'color_badge' => 'bg-amber-100 text-amber-800 border-amber-300',
                'explicacion' => 'Descenso rápido de pH, posible pérdida de capacidad de retención de agua.',
            ];
        }

        if ($ph <= 5.75) {
            return [
                'estado'      => 'pH Óptimo (Normal / Excelente)',
                'color_badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'explicacion' => 'Reserva adecuada de glucógeno post-mortem. Color rojo cereza brillante y vida de anaquel ideal.',
            ];
        }

        return [
            'estado'      => 'pH Elevado (Corte Oscuro DFD)',
            'color_badge' => 'bg-rose-100 text-rose-800 border-rose-300',
            'explicacion' => 'Agotamiento de glucógeno muscular por estrés previo al sacrificio. Carne oscura, firme y seca.',
        ];
    }
}
