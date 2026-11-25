<?php

namespace App\Support;

use Database\Seeders\SimulacionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Datos de prueba: se cargan con la simulación y se borran con un clic.
 * Cada fila que crea la simulación queda anotada en `simulacion_registros`; al eliminar
 * se borran exactamente esas filas (en orden inverso), sin tocar lo que se capturó a mano.
 */
class Simulacion
{
    private const TABLA = 'simulacion_registros';

    /** Modelos que nunca se anotan (catálogo del sistema). */
    private const EXCLUIR = [Permission::class, Role::class];

    public static function activa(): bool
    {
        return Schema::hasTable(self::TABLA) && DB::table(self::TABLA)->exists();
    }

    public static function resumen(): array
    {
        if (! Schema::hasTable(self::TABLA)) {
            return ['total' => 0, 'tablas' => [], 'desde' => null];
        }

        return [
            'total'  => DB::table(self::TABLA)->count(),
            'tablas' => DB::table(self::TABLA)->selectRaw('tabla, count(*) as total')->groupBy('tabla')->orderByDesc('total')->pluck('total', 'tabla')->all(),
            'desde'  => ($d = DB::table(self::TABLA)->min('created_at')) ? Carbon::parse($d) : null,
        ];
    }

    /** La simulación solo se carga sobre una operación vacía (sin ganado capturado). */
    public static function sePuedeCargar(): bool
    {
        return ! self::activa() && ! DB::table('ganado')->exists();
    }

    /** Ejecuta $fn anotando todo lo que se cree con Eloquent. */
    public static function rastrear(callable $fn): mixed
    {
        $ahora = now();
        $escucha = function (string $evento, array $datos) use ($ahora) {
            $modelo = $datos[0] ?? null;
            if (! $modelo instanceof Model || in_array($modelo::class, self::EXCLUIR, true) || $modelo->getTable() === self::TABLA) {
                return;
            }
            DB::table(self::TABLA)->insert([
                'tabla' => $modelo->getTable(), 'llave' => $modelo->getKeyName(), 'valor' => (string) $modelo->getKey(), 'created_at' => $ahora,
            ]);
        };
        Event::listen('eloquent.created: *', $escucha);

        try {
            return $fn();
        } finally {
            Event::forget('eloquent.created: *');
        }
    }

    /** Carga la simulación (la llama el botón del Súper Administrador). Devuelve la clave de los usuarios de prueba. */
    public static function cargar(): string
    {
        @set_time_limit(300);
        $seeder = app(SimulacionSeeder::class);
        $seeder->setContainer(app());
        $seeder->permitirEnProduccion = true;
        $seeder->run();

        return SimulacionSeeder::CLAVE_USUARIOS;
    }

    /** Borra todas las filas creadas por la simulación. Devuelve cuántas se borraron. */
    public static function eliminar(): int
    {
        @set_time_limit(300);
        $registros = DB::table(self::TABLA)->orderByDesc('id')->get();
        $usuarios = $registros->where('tabla', 'users')->pluck('valor')->all();

        // Archivos que la simulación pudiera haber dejado (fotos, documentos, capturas)
        foreach (['ganado_fotos' => 'ruta', 'documentos' => 'ruta', 'preguntas_ayuda' => 'captura_path'] as $tabla => $columna) {
            $ids = $registros->where('tabla', $tabla)->pluck('valor')->all();
            if ($ids && Schema::hasTable($tabla)) {
                foreach (DB::table($tabla)->whereIn('id', $ids)->whereNotNull($columna)->pluck($columna) as $ruta) {
                    Storage::disk('local')->delete($ruta);
                }
            }
        }

        Schema::disableForeignKeyConstraints();
        try {
            DB::transaction(function () use ($registros, $usuarios) {
                foreach ($registros->chunk(500) as $bloque) {
                    foreach ($bloque->groupBy(fn ($r) => $r->tabla . '|' . $r->llave) as $clave => $filas) {
                        [$tabla, $llave] = explode('|', $clave);
                        if (Schema::hasTable($tabla)) {
                            DB::table($tabla)->whereIn($llave, $filas->pluck('valor')->all())->delete();
                        }
                    }
                }
                if ($usuarios) {
                    $tipo = \App\Models\User::class;
                    DB::table(config('permission.table_names.model_has_roles'))->where('model_type', $tipo)->whereIn('model_id', $usuarios)->delete();
                    DB::table(config('permission.table_names.model_has_permissions'))->where('model_type', $tipo)->whereIn('model_id', $usuarios)->delete();
                }
                DB::table(self::TABLA)->delete();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Queda constancia en la bitácora
        \App\Models\Auditoria::create([
            'user_id' => auth()->id(), 'accion' => 'eliminado', 'modelo' => 'Simulacion', 'modelo_id' => null,
            'descripcion' => "Datos de prueba eliminados ({$registros->count()} registros)",
            'ip' => request()?->ip(), 'url' => app()->runningInConsole() ? 'consola' : request()?->fullUrl(),
        ]);

        return $registros->count();
    }
}
