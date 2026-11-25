<?php

namespace App\Models\Ganaderia;

use App\Models\Concerns\Auditable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/** Archivo adjunto (PDF/imagen) ligado a una compra, venta, animal, gasto, etc. */
class Documento extends Model
{
    use Auditable;

    protected $table = 'documentos';

    public const DISCO = 'local'; // privado: storage/app/private

    public const TIPOS = [
        'guia_reemo'          => 'Guía REEMO / Movilización',
        'certificado_zoo'     => 'Certificado zoosanitario',
        'dictamen_tb_br'      => 'Dictamen TB / Brucelosis',
        'cfdi'                => 'Factura CFDI (PDF/XML)',
        'comprobante_pago'    => 'Comprobante de pago',
        'contrato'            => 'Contrato',
        'certificado_rastro'  => 'Certificado de rastro / TIF',
        'foto'                => 'Fotografía',
        'otro'                => 'Otro',
    ];

    /** Modelos a los que se pueden adjuntar documentos (alias => clase). */
    public const ADJUNTABLES = [
        'compra'  => \App\Models\Ganado\FacturaGanado::class,
        'venta'   => \App\Models\Venta\VentaGanado::class,
        'animal'  => \App\Models\Ganado\Ganado::class,
        'gasto'   => GastoOperativo::class,
        'baja'    => \App\Models\Ganado\BajaGanado::class,
    ];

    protected $fillable = [
        'documentable_type', 'documentable_id', 'tipo', 'folio', 'fecha', 'ruta', 'nombre_original', 'mime', 'tamano', 'notas', 'user_id',
    ];

    protected $casts = ['fecha' => 'date'];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tipoLegible(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    public function tamanoLegible(): string
    {
        $kb = $this->tamano / 1024;

        return $kb > 1024 ? number_format($kb / 1024, 1) . ' MB' : number_format($kb, 0) . ' KB';
    }

    protected static function booted(): void
    {
        static::deleted(fn (Documento $d) => Storage::disk(self::DISCO)->delete($d->ruta));
    }

    public function descripcionAuditoria(): string
    {
        return $this->tipoLegible() . ': ' . $this->nombre_original;
    }
}
