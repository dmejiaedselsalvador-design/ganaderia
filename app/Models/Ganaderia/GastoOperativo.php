<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\TieneDocumentos;
use App\Models\Engorda\CorralLote;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoOperativo extends Model
{
    use Auditable, TieneDocumentos;

    protected $table = 'gastos_operativos';

    public const CATEGORIAS = [
        'Mano de obra', 'Combustible', 'Electricidad', 'Agua', 'Mantenimiento', 'Renta de terreno',
        'Fletes', 'Comisiones', 'Honorarios veterinarios', 'Seguros', 'Impuestos y derechos',
        'Herramientas y equipo', 'Administración', 'Otros',
    ];

    public const FORMAS_PAGO = ['Efectivo', 'Transferencia', 'Cheque', 'Tarjeta', 'Crédito'];

    protected $fillable = [
        'fecha', 'categoria', 'concepto', 'monto', 'proveedor', 'forma_pago', 'folio_cfdi',
        'corral_lote_id', 'ubicacion_id', 'estado', 'motivo_anulacion', 'notas', 'user_id',
    ];

    protected $casts = ['fecha' => 'date', 'monto' => 'decimal:2'];

    public function scopeVigentes(Builder $q): Builder
    {
        return $q->where('estado', 'vigente');
    }

    public function corral(): BelongsTo
    {
        return $this->belongsTo(CorralLote::class, 'corral_lote_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function descripcionAuditoria(): string
    {
        return "{$this->categoria}: {$this->concepto}";
    }
}
