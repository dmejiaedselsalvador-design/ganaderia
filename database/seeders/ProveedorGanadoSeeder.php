<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedorGanadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('proveedorGanado')->insert([
            [
                'nombreProveedor' => 'Ganadería San José',
                'nombreContacto' => 'Don Mario Meléndez',
                'telefono' => '7890-1234',
                'lugar' => 'San Vicente',
                'ubicacion' => 'Km 60 Carretera Panamericana, Cantón San Esteban',
                'razon_social' => 'Ganadería San José S.A. de C.V.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombreProoveedor' => 'Agropecuaria El Progreso',
                'nombreContacto' => 'Ing. Roberto Cárdenas',
                'telefono' => '7211-4589',
                'lugar' => 'Santa Ana',
                'ubicacion' => 'Cantón Las Cruces, Metapán',
                'razon_social' => 'El Progreso de Occidente S.A.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombreProoveedor' => 'Finca Los Amates',
                'nombreContacto' => 'Lic. Carlos Ramírez',
                'telefono' => '7033-9871',
                'lugar' => 'Usulután',
                'ubicacion' => 'Zacatecoluca, Jurisdicción de San Miguel',
                'razon_social' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
