<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verifica si el usuario ya existe para evitar duplicados si corres el seeder varias veces
        User::firstOrCreate(
            ['email' => 'luis@test.com'],
            [
                'name' => 'Luis David', // O puedes ponerle 'Admin'
                'password' => Hash::make('123456789'),
                // Si tienes una columna para roles o tipo de usuario (ej. 'role' => 'admin'), puedes descomentar la siguiente línea:
                // 'role' => 'admin',
            ]
        );
    }
}
