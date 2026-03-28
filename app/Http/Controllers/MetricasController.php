<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricasController extends Controller
{
    public function index()
    {
        // Solo administradores pueden ver métricas
        if (auth()->user()->rol !== 'Administrador') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $hoy = Carbon::now('America/Mexico_City')->startOfDay();
        $mañana = (clone $hoy)->addDay();
        $hace7Dias = (clone $hoy)->subDays(7);

        // ── Cards superiores ──────────────────────────────────
        $ventasTotales    = Venta::where('created_at', '>=', $hace7Dias)->sum('total') ?: 0;
        $totalTransacciones = Venta::where('created_at', '>=', $hace7Dias)->count();

        // Turno con más ventas
        $turnoDestacado = Venta::select('turno', DB::raw('SUM(total) as suma'))
            ->where('created_at', '>=', $hace7Dias)
            ->groupBy('turno')
            ->orderByDesc('suma')
            ->first();

        // ── Ventas por día (últimos 7 días) ───────────────────
        $ventasPorDia = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dia = (clone $hoy)->subDays($i);
            $diaSiguiente = (clone $dia)->addDay();
            $ventasPorDia->push([
                'dia'   => $dia->translatedFormat('D'), // Lun, Mar...
                'total' => Venta::whereBetween('created_at', [$dia, $diaSiguiente])->sum('total') ?: 0,
            ]);
        }

        // ── Métodos de pago ───────────────────────────────────
        $metodosPago = Venta::select('metodo_pago', DB::raw('SUM(total) as suma'))
            ->where('created_at', '>=', $hace7Dias)
            ->groupBy('metodo_pago')
            ->get()
            ->mapWithKeys(fn($v) => [$v->metodo_pago => (float) $v->suma]);

        return view('metricas', compact(
            'ventasTotales',
            'totalTransacciones',
            'turnoDestacado',
            'ventasPorDia',
            'metodosPago'
        ));
    }
}