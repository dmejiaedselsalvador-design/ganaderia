<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'accion', 'modelo', 'modelo_id', 'descripcion', 'antes', 'despues', 'ip', 'url'];

    protected $casts = [
        'antes'      => 'array',
        'despues'    => 'array',
        'created_at' => 'datetime',
    ];

    /** Nombres legibles de los modelos auditados. */
    public const MODELOS = [
        'Ganado' => 'Animal', 'PesajeGanado' => 'Pesaje', 'BajaGanado' => 'Baja', 'VentaGanado' => 'Venta',
        'AbonoVenta' => 'Abono', 'FacturaGanado' => 'Guía de compra', 'ProveedorGanado' => 'Proveedor',
        'Adelanto' => 'Anticipo', 'CorralLote' => 'Corral', 'RacionAlimento' => 'Ración', 'ConsumoAlimento' => 'Consumo de alimento',
        'TratamientoSanitario' => 'Tratamiento', 'EvaluacionCanal' => 'Evaluación de canal', 'User' => 'Usuario',
        'Cliente' => 'Cliente', 'GastoOperativo' => 'Gasto', 'Insumo' => 'Insumo', 'MovimientoInsumo' => 'Movimiento de inventario',
        'PrecioMercado' => 'Precio de mercado', 'Ubicacion' => 'Ubicación', 'ProgramacionSanitaria' => 'Programación sanitaria',
        'EventoEnfermedad' => 'Evento de enfermedad', 'Documento' => 'Documento', 'MovimientoCorral' => 'Movimiento de corral',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modeloLegible(): string
    {
        return self::MODELOS[$this->modelo] ?? $this->modelo;
    }
}
