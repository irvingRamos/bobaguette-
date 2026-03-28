<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use Carbon\Carbon;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar ventas previas para evitar duplicidad si se desea
        Venta::truncate();

        $metodos = ['Efectivo', 'Tarjeta', 'Transferencia'];
        $turnos  = ['Matutino', 'Vespertino', 'Parcial'];

        // Generar ventas para los últimos 7 días (incluyendo hoy)
        for ($i = 6; $i >= 0; $i--) {
            $fechaBase = Carbon::today()->subDays($i);

            // Entre 5 y 12 ventas por día para que las gráficas se vean llenas
            $numVentas = rand(5, 12);

            for ($j = 0; $j < $numVentas; $j++) {
                // Crear una hora aleatoria para la venta
                $fechaConHora = $fechaBase->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));
                
                Venta::create([
                    'total'       => rand(50, 850),
                    'metodo_pago' => $metodos[array_rand($metodos)],
                    'turno'       => $turnos[array_rand($turnos)],
                    'user_id'     => 1, // Asumiendo que el ID 1 existe
                    'created_at'  => $fechaConHora,
                    'updated_at'  => $fechaConHora,
                ]);
            }
        }
    }
}