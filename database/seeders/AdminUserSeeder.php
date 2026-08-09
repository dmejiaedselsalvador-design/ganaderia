<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; // Asegúrate de importar el modelo Role

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear los roles si no existen
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'operador']);
        Role::firstOrCreate(['name' => 'ventas']);

        // 2. Crear el usuario administrador
        $user = User::firstOrCreate(
            ['email' => 'luis@test.com'],
            [
                'name' => 'Luis David',
                'password' => Hash::make('123456789'),
            ]
        );

        // 3. Asignarle el rol al usuario
        $user->assignRole($roleAdmin);
    }
}
