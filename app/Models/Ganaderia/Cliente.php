<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\Venta\VentaGanado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'clientes';

    /** Regímenes fiscales SAT más comunes. */
    public const REGIMENES = [
        '601' => 'General de Ley Personas Morales',
        '603' => 'Personas Morales con Fines no Lucrativos',
        '605' => 'Sueldos y Salarios',
        '606' => 'Arrendamiento',
        '612' => 'Personas Físicas con Actividades Empresariales',
        '616' => 'Sin obligaciones fiscales',
        '621' => 'Incorporación Fiscal',
        '622' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
        '625' => 'Plataformas Tecnológicas',
        '626' => 'Régimen Simplificado de Confianza (RESICO)',
    ];

    protected $fillable = [
        'nombre', 'razon_social', 'rfc', 'regimen_fiscal', 'uso_cfdi', 'codigo_postal', 'telefono', 'email',
        'direccion', 'contacto', 'limite_credito', 'dias_credito', 'activo', 'notas',
    ];

    protected $casts = [
        'activo'         => 'boolean',
        'limite_credito' => 'decimal:2',
        'dias_credito'   => 'integer',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaGanado::class, 'cliente_id');
    }

    /** Saldo total por cobrar (ventas completadas a crédito con saldo). */
    public function saldoPendiente(): float
    {
        return (float) $this->ventas()->where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('saldo_pendiente');
    }

    public function saldoVencido(): float
    {
        return (float) $this->ventas()->where('estado', VentaGanado::ESTADO_COMPLETADA)
            ->where('saldo_pendiente', '>', 0)->whereDate('fecha_vencimiento', '<', now())->sum('saldo_pendiente');
    }

    public function creditoDisponible(): ?float
    {
        return $this->limite_credito > 0 ? max(0, (float) $this->limite_credito - $this->saldoPendiente()) : null;
    }

    public function descripcionAuditoria(): string
    {
        return $this->nombre;
    }
}
