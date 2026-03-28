<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Postres
            ['nombre' => 'Crepas dulces',  'categoria' => 'Postres',   'precio' => 80.00, 'imagen' => 'https://images.unsplash.com/photo-1519676867240-f03562e64548?w=400&q=80'],
            ['nombre' => 'Crepas saladas', 'categoria' => 'Postres',   'precio' => 85.00, 'imagen' => 'https://images.unsplash.com/photo-1565299543923-37dd37887442?w=400&q=80'],
            ['nombre' => 'Pastes',         'categoria' => 'Postres',   'precio' => 45.00, 'imagen' => 'https://images.unsplash.com/photo-1621955964441-c173e01c135b?w=400&q=80'],
            // Alimentos
            ['nombre' => 'Baguette',       'categoria' => 'Alimentos', 'precio' => 65.00, 'imagen' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc7c?w=400&q=80'],
            ['nombre' => 'Sandwich',       'categoria' => 'Alimentos', 'precio' => 75.00, 'imagen' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=400&q=80'],
            ['nombre' => 'Ensalada',       'categoria' => 'Alimentos', 'precio' => 70.00, 'imagen' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80'],
            // Bebidas
            ['nombre' => 'Bebidas calientes', 'categoria' => 'Bebidas', 'precio' => 55.00, 'imagen' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=400&q=80'],
            ['nombre' => 'Fra-T',           'categoria' => 'Bebidas',  'precio' => 65.00, 'imagen' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&q=80'],
            ['nombre' => 'Agua fresca',     'categoria' => 'Bebidas',  'precio' => 35.00, 'imagen' => 'https://images.unsplash.com/photo-1497534446932-c925b458314e?w=400&q=80'],
        ];

        foreach ($productos as $p) {
            Producto::create($p);
        }
    }
}