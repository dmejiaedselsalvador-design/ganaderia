<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos de DEMOSTRACIÓN (proveedores, corrales, animales, pesajes...).
 * Nunca se ejecuta en producción.
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder está bloqueado en producción (APP_ENV=production).');

            return;
        }

        $this->call([
            ProveedorGanadoSeeder::class,
            EngordaYCalidadSeeder::class,
        ]);
    }
}
