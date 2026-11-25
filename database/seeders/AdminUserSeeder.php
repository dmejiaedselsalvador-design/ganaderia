<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ClaveTemporal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crea la cuenta inicial de Súper Administrador.
 *
 * Datos tomados del .env (o preguntados en la terminal si faltan):
 *   ADMIN_NAME="Nombre Apellido"
 *   ADMIN_EMAIL=correo@empresa.com
 *
 * La clave es TEMPORAL y se muestra una sola vez en la terminal;
 * en el primer ingreso el sistema obliga a cambiarla.
 * Si el correo ya existe, no se modifica su clave.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('auth.admin_inicial.email') ?: $this->command?->ask('Correo del Súper Administrador');
        $nombre = config('auth.admin_inicial.name') ?: $this->command?->ask('Nombre del Súper Administrador', 'Administrador');

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command?->error('Correo inválido: define ADMIN_EMAIL en el .env y vuelve a ejecutar el seeder.');

            return;
        }

        $user = User::where('email', $email)->first();
        $clave = null;

        if (! $user) {
            $clave = ClaveTemporal::generar();
            $user = User::create([
                'name'                 => $nombre,
                'email'                => $email,
                'password'             => Hash::make($clave),
                'must_change_password' => true,
                'status'               => User::ESTADO_ACTIVO,
            ]);
        }

        $user->syncRoles(['super-admin']);
        $user->givePermissionTo(['planta.ganaderia', 'planta.construccion']);

        if ($clave) {
            $this->command?->newLine();
            $this->command?->warn('  Súper Administrador creado. Guarda estos datos (no se vuelven a mostrar):');
            $this->command?->line("  Usuario:        {$email}");
            $this->command?->line("  Clave temporal: {$clave}");
            $this->command?->line('  Deberá cambiarla en su primer ingreso.');
            $this->command?->newLine();
        } else {
            $this->command?->info("  El usuario {$email} ya existía: se confirmó como Súper Administrador (clave sin cambios).");
        }
    }
}
