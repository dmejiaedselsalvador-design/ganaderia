<?php

namespace App\Models\Ganado;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ganado extends Model
{
    use HasFactory;

    // Nombre explícito de la tabla en tu base de datos
    protected $table = 'ganado';

    // Campos permitidos para asignación masiva (Mass Assignment)
    protected $fillable = [
        'facturaID',
        'areteID',
        'raza',
        'genero',
        'pesoActual',
        'ultimoPeso',
        'precioCompra',
        'fechaCompra',
        'status',
        'notas',
        'foto',
    ];

    // Conversión automática de tipos de datos para evitar errores con decimales y fechas
    protected $casts = [
        'pesoActual' => 'decimal:2',
        'ultimoPeso' => 'decimal:2',
        'precioCompra' => 'decimal:2',
        'fechaCompra' => 'date',
    ];

    /**
     * Relación: Un animal pertenece a una factura o lote de compra.
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaGanado::class, 'facturaID');
    }
}
