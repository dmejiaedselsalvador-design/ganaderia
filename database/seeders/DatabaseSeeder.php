<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos base para PRODUCCIÓN (sin datos de prueba):
     *  - Roles y permisos del sistema
     *  - Cuenta inicial de Súper Administrador con clave temporal
     *
     * Los datos de demostración están en DemoSeeder y solo se cargan a mano
     * en entornos locales:  php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $this->call([
            RolesYPermisosSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
