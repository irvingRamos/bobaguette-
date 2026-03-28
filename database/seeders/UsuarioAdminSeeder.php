<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos a tu usuario administrador
        User::create([
            'name' => 'Irvin Ramos',
            'email' => 'admin@bobaguette.com',
            'password' => Hash::make('admin123'),
            'rol' => 'Administrador',
            'turno' => 'Parcial',
        ]);

        // Usuario de prueba para cajero
        User::create([
            'name' => 'Cajero Uno',
            'email' => 'cajero1@bobaguette.com',
            'password' => Hash::make('caja123'),
            'rol' => 'Trabajador',
            'turno' => 'Vespertino',
        ]);
    }
}