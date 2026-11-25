<?php

namespace App\Models\Concerns;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

/**
 * Registra en la bitácora (tabla auditoria) cada alta, cambio y eliminación del modelo.
 * Uso: agregar `use Auditable;` al modelo.
 */
trait Auditable
{
    /** Campos que nunca se guardan en la bitácora. */
    protected static array $auditoriaOcultos = ['password', 'remember_token', 'clave_cifrada', 'token_hash', 'updated_at', 'created_at'];

    public static function bootAuditable(): void
    {
        static::created(fn (Model $m) => static::registrarAuditoria($m, 'creado', null, $m->getAttributes()));

        static::updated(function (Model $m) {
            $cambios = $m->getChanges();
            unset($cambios['updated_at']);
            $ignorar = method_exists($m, 'camposNoAuditables') ? $m->camposNoAuditables() : [];
            $cambios = array_diff_key($cambios, array_flip($ignorar));
            if (empty($cambios)) {
                return;
            }
            $antes = array_intersect_key($m->getOriginal(), $cambios);
            static::registrarAuditoria($m, 'actualizado', $antes, $cambios);
        });

        static::deleted(fn (Model $m) => static::registrarAuditoria($m, 'eliminado', $m->getAttributes(), null));

        if (method_exists(static::class, 'restored')) {
            static::restored(fn (Model $m) => static::registrarAuditoria($m, 'restaurado', null, $m->getAttributes()));
        }
    }

    protected static function registrarAuditoria(Model $m, string $accion, ?array $antes, ?array $despues): void
    {
        $limpiar = function (?array $datos) {
            if ($datos === null) {
                return null;
            }
            foreach (static::$auditoriaOcultos as $campo) {
                unset($datos[$campo]);
            }
            foreach ($datos as $k => $v) {
                if ($v instanceof \DateTimeInterface) {
                    $datos[$k] = $v->format('Y-m-d H:i:s');
                } elseif (is_string($v) && mb_strlen($v) > 500) {
                    $datos[$k] = mb_substr($v, 0, 500) . '…';
                }
            }

            return $datos;
        };

        try {
            $request = app()->runningInConsole() ? null : request();

            Auditoria::create([
                'user_id'     => auth()->id(),
                'accion'      => $accion,
                'modelo'      => class_basename($m),
                'modelo_id'   => (string) $m->getKey(),
                'descripcion' => method_exists($m, 'descripcionAuditoria') ? $m->descripcionAuditoria() : null,
                'antes'       => $limpiar($antes),
                'despues'     => $limpiar($despues),
                'ip'          => $request?->ip(),
                'url'         => $request ? mb_substr($request->method() . ' ' . $request->path(), 0, 500) : 'consola',
            ]);
        } catch (\Throwable $e) {
            // La bitácora nunca debe impedir la operación principal
            report($e);
        }
    }
}
