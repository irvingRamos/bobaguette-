<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Insumo;

class InsumoSeeder extends Seeder
{
    public function run(): void
    {
        $insumos = [
            // Bebidas
            ['nombre' => 'Leche entera',    'categoria' => 'Bebidas',   'cantidad' => 20, 'unidad' => 'Litros', 'nivel_minimo' => 5],
            ['nombre' => 'Café en grano',   'categoria' => 'Bebidas',   'cantidad' => 10, 'unidad' => 'Kg',     'nivel_minimo' => 2],
            ['nombre' => 'Jarabe vainilla', 'categoria' => 'Bebidas',   'cantidad' => 5,  'unidad' => 'Botellas', 'nivel_minimo' => 2],
            ['nombre' => 'Té matcha',       'categoria' => 'Bebidas',   'cantidad' => 2,  'unidad' => 'Kg',     'nivel_minimo' => 1],
            
            // Alimentos
            ['nombre' => 'Harina',          'categoria' => 'Alimentos', 'cantidad' => 50, 'unidad' => 'Kg',     'nivel_minimo' => 10],
            ['nombre' => 'Jamón',           'categoria' => 'Alimentos', 'cantidad' => 8,  'unidad' => 'Kg',     'nivel_minimo' => 2],
            ['nombre' => 'Queso Manchego',  'categoria' => 'Alimentos', 'cantidad' => 10, 'unidad' => 'Kg',     'nivel_minimo' => 3],
            ['nombre' => 'Lechuga',         'categoria' => 'Alimentos', 'cantidad' => 15, 'unidad' => 'Piezas', 'nivel_minimo' => 5],
            
            // Postres
            ['nombre' => 'Chocolate',       'categoria' => 'Postres',   'cantidad' => 12, 'unidad' => 'Kg',     'nivel_minimo' => 3],
            ['nombre' => 'Fresas',          'categoria' => 'Postres',   'cantidad' => 5,  'unidad' => 'Kg',     'nivel_minimo' => 2],
            ['nombre' => 'Mantequilla',     'categoria' => 'Postres',   'cantidad' => 15, 'unidad' => 'Kg',     'nivel_minimo' => 5],
        ];

        foreach ($insumos as $i) {
            Insumo::create($i);
        }
    }
}
