<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea los roles/permisos y el usuario Súper Administrador inicial.
     */
    public function run(): void
    {
        $this->call(RolesYPermisosSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'luis@test.com'],
            [
                'name'     => 'Luis David',
                'password' => Hash::make('123456789'),
                'status'   => User::ESTADO_ACTIVO,
            ]
        );

        $user->syncRoles(['super-admin']);
        $user->givePermissionTo(['planta.ganaderia', 'planta.construccion']);
    }
}
