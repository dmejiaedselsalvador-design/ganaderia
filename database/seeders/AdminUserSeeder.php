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
    //    $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
   //     Role::firstOrCreate(['name' => 'operador']);
   //     Role::firstOrCreate(['name' => 'ventas']);
   Role::updateOrCreate(['name' => 'super-admin'],
   [
    'display_name' => 'dueño',
   'description' => 'Dueño de la ganadería y responsable de la gestión general del sistema.']
   );

   Role::updateOrCreate(['name' => 'admin'],
                [
                    'display_name' => 'administrador',
                    'description' => 'Control total del sistema de ganadería y gestión de usuarios.',

                    ]);
                    Role::updateOrCreate(['name' => 'operador'],
                [
                    'display_name' => 'operador',
                    'description' => 'Encargado del registro y visualización del ganado en los corrales.',
                    ]);
                    Role::updateOrCreate(['name' => 'ventas'],
                [
                    'display_name' => 'ventas',
                    'description' => 'Responsable de la comercialización, compra de animales y avances a proveedores.',
                    ]);



        // 2. Crear el usuario administrador
        $user = User::firstOrCreate(
            ['email' => 'luis@test.com'],
            [
                'name' => 'Luis David',
                'password' => Hash::make('123456789'),
                'status' => 'active', // Asegúrate de establecer el estado como activo
            ]
        );

        // 3. Asignarle el rol al usuario
        $user->assignRole('admin');
    }
}
