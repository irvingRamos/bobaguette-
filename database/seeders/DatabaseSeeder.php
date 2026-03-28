<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Esta línea es la que "despierta" a tu otro archivo
        $this->call([
            UsuarioAdminSeeder::class,
            ProductoSeeder::class,
            InsumoSeeder::class,
        ]);
    }
}
