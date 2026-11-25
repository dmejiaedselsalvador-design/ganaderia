<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permisos;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea/actualiza permisos y roles base. Es seguro ejecutarlo varias veces:
 *   php artisan db:seed --class=RolesYPermisosSeeder
 *
 * También migra los roles antiguos:
 *   admin    -> super-admin (los usuarios con 'admin' pasan a super-admin)
 *   operador -> operario
 *   ventas   -> vendedor
 */
class RolesYPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Permisos (se recuerdan los que se crean por primera vez en esta ejecución)
        $nuevos = [];
        foreach (Permisos::GRUPOS as $grupo => $permisos) {
            foreach ($permisos as $nombre => $etiqueta) {
                $permiso = Permission::updateOrCreate(
                    ['name' => $nombre, 'guard_name' => 'web'],
                    ['display_name' => $etiqueta, 'description' => $grupo]
                );
                if ($permiso->wasRecentlyCreated) {
                    $nuevos[] = $nombre;
                }
            }
        }

        // 2. Renombrar roles antiguos conservando sus asignaciones
        foreach (['operador' => 'operario', 'ventas' => 'vendedor'] as $viejo => $nuevo) {
            $rolViejo = Role::where('name', $viejo)->where('guard_name', 'web')->first();
            if ($rolViejo && ! Role::where('name', $nuevo)->where('guard_name', 'web')->exists()) {
                $rolViejo->update(['name' => $nuevo]);
            }
        }

        // 3. Roles base
        foreach (Permisos::ROLES as $nombre => [$display, $descripcion, $nivel, $permisos]) {
            $rol = Role::updateOrCreate(
                ['name' => $nombre, 'guard_name' => 'web'],
                ['display_name' => $display, 'description' => $descripcion, 'nivel' => $nivel]
            );

            // Solo se asignan permisos por defecto a roles recién creados o sin permisos,
            // para no pisar los cambios hechos desde la pantalla de Roles.
            if ($permisos !== '*' && $rol->permissions()->count() === 0) {
                $rol->syncPermissions($permisos);
            } elseif ($permisos !== '*' && $nuevos) {
                // Permisos de módulos nuevos: se agregan a los roles base que los incluyen por defecto
                $rol->givePermissionTo(array_values(array_intersect($permisos, $nuevos)));
            }
        }

        // 4. Rol antiguo 'admin' -> super-admin
        if ($admin = Role::where('name', 'admin')->where('guard_name', 'web')->first()) {
            foreach (User::role('admin')->get() as $u) {
                $u->assignRole('super-admin');
                $u->removeRole('admin');
            }
            $admin->delete();
        }

        // 5. Compatibilidad: usuarios existentes sin acceso a planta reciben Ganadería
        $permisosPlanta = array_keys(Permisos::GRUPOS['Acceso a plantas']);
        User::query()->each(function (User $u) use ($permisosPlanta) {
            if ($u->getDirectPermissions()->whereIn('name', $permisosPlanta)->isEmpty()) {
                $u->givePermissionTo('planta.ganaderia');
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
