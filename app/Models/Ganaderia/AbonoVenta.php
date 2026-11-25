<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\User;
use App\Models\Venta\VentaGanado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pago recibido de un cliente sobre una venta. */
class AbonoVenta extends Model
{
    use Auditable;

    protected $table = 'abonos_venta';

    public const FORMAS_PAGO = ['Efectivo', 'Transferencia', 'Cheque', 'Depósito', 'Tarjeta'];

    protected $fillable = ['venta_id', 'fecha', 'monto', 'forma_pago', 'referencia', 'estado', 'motivo_anulacion', 'notas', 'user_id'];

    protected $casts = ['fecha' => 'date', 'monto' => 'decimal:2'];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(VentaGanado::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function descripcionAuditoria(): string
    {
        return 'Abono ' . number_format((float) $this->monto, 2) . ' a venta #' . $this->venta_id;
    }
}
