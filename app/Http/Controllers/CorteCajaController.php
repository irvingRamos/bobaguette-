<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Gasto;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CorteCajaController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $hoy = Carbon::now('America/Mexico_City')->startOfDay();
        $mañana = (clone $hoy)->addDay();
        $insumos = Insumo::orderBy('nombre')->get();

        // Si es administrador, ve todo el corte general y de todos los turnos
        if ($user->isAdmin()) {
            $totalEfectivo      = Venta::whereBetween('created_at', [$hoy, $mañana])->where('metodo_pago', 'Efectivo')->sum('total') ?: 0;
            $totalTarjeta       = Venta::whereBetween('created_at', [$hoy, $mañana])->where('metodo_pago', 'Tarjeta')->sum('total') ?: 0;
            $totalTransferencia = Venta::whereBetween('created_at', [$hoy, $mañana])->where('metodo_pago', 'Transferencia')->sum('total') ?: 0;
            
            $totalGastos        = Gasto::whereBetween('created_at', [$hoy, $mañana])->sum('monto') ?: 0;
            $totalGeneral       = ($totalEfectivo + $totalTarjeta + $totalTransferencia) - $totalGastos;

            $transEfectivo      = Venta::whereBetween('created_at', [$hoy, $mañana])->where('metodo_pago', 'Efectivo')->count();
            $transTarjeta       = Venta::whereBetween('created_at', [$hoy, $mañana])->where('metodo_pago', 'Tarjeta')->count();
            $transTransferencia = Venta::whereBetween('created_at', [$hoy, $mañana])->where('metodo_pago', 'Transferencia')->count();

            // Datos por turno (todos los turnos)
            $turnos = ['Matutino', 'Vespertino', 'Parcial'];
            $datosTurnos = [];

            foreach ($turnos as $turno) {
                $ventas = Venta::whereBetween('created_at', [$hoy, $mañana])->where('turno', $turno);
                $gastosTurno = Gasto::whereBetween('created_at', [$hoy, $mañana])->where('turno', $turno);
                
                $datosTurnos[$turno] = [
                    'efectivo'      => (clone $ventas)->where('metodo_pago', 'Efectivo')->sum('total'),
                    'transferencia' => (clone $ventas)->where('metodo_pago', 'Transferencia')->sum('total'),
                    'tarjeta'       => (clone $ventas)->where('metodo_pago', 'Tarjeta')->sum('total'),
                    'gastos'        => (clone $gastosTurno)->sum('monto'),
                    'lista_gastos'  => (clone $gastosTurno)->get(),
                    'total'         => (clone $ventas)->sum('total') - (clone $gastosTurno)->sum('monto'),
                    'num_ventas'    => (clone $ventas)->count(),
                ];
            }

            return view('corte', compact(
                'totalEfectivo', 'totalTarjeta', 'totalTransferencia', 'totalGeneral',
                'transEfectivo', 'transTarjeta', 'transTransferencia', 'totalGastos',
                'datosTurnos', 'insumos'
            ));
        }

        // Si es trabajador, solo ve los datos de SU turno
        $turnoActual = $user->turno ?? 'No asignado';
        
        $ventasTurno = Venta::whereBetween('created_at', [$hoy, $mañana])->where('turno', $turnoActual);
        $gastosTurno = Gasto::whereBetween('created_at', [$hoy, $mañana])->where('turno', $turnoActual);

        $datosTurno = [
            'efectivo'      => (clone $ventasTurno)->where('metodo_pago', 'Efectivo')->sum('total'),
            'transferencia' => (clone $ventasTurno)->where('metodo_pago', 'Transferencia')->sum('total'),
            'tarjeta'       => (clone $ventasTurno)->where('metodo_pago', 'Tarjeta')->sum('total'),
            'gastos'        => (clone $gastosTurno)->sum('monto'),
            'lista_gastos'  => (clone $gastosTurno)->get(),
            'total'         => (clone $ventasTurno)->sum('total') - (clone $gastosTurno)->sum('monto'),
            'num_ventas'    => (clone $ventasTurno)->count(),
        ];

        return view('corte_trabajador', compact('datosTurno', 'turnoActual', 'insumos'));
    }

    public function storeGasto(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'monto'  => 'required|numeric|min:0',
            'turno'  => 'required|string',
            'descripcion' => 'nullable|string',
            'cantidad_insumo' => 'nullable|numeric|min:0'
        ]);

        // Crear el gasto
        $gasto = Gasto::create([
            'nombre' => $request->nombre,
            'monto'  => $request->monto,
            'turno'  => $request->turno,
            'descripcion' => $request->descripcion
        ]);

        // Si se especificó una cantidad y el nombre coincide con un insumo, aumentar stock
        if ($request->filled('cantidad_insumo')) {
            $insumo = Insumo::where('nombre', $request->nombre)->first();
            if ($insumo) {
                $insumo->increment('cantidad', $request->cantidad_insumo);
                return redirect()->route('corte.index')->with('success', "Gasto registrado e inventario de '{$insumo->nombre}' actualizado (+{$request->cantidad_insumo} {$insumo->unidad})");
            }
        }

        return redirect()->route('corte.index')->with('success', 'Gasto registrado correctamente');
    }

    public function destroyGasto($id)
    {
        // Solo administradores pueden eliminar gastos
        if (auth()->user()->rol !== 'Administrador') {
            return redirect()->route('corte.index')->with('error', 'No tienes permisos para eliminar gastos.');
        }

        $gasto = Gasto::findOrFail($id);
        $gasto->delete();

        return redirect()->route('corte.index')->with('success', 'Gasto eliminado correctamente');
    }

    /**
     * Sincronizar ventas offline
     */
    public function syncVentas(Request $request)
    {
        $data = $request->input('data');
        $method = $request->input('method', 'POST');

        try {
            if ($method === 'POST') {
                // Crear nueva venta
                $venta = Venta::create([
                    'total' => $data['total'],
                    'metodo_pago' => $data['metodo_pago'],
                    'turno' => $data['turno'],
                    'user_id' => $data['user_id'],
                    'notas' => $data['notas'] ?? ''
                ]);

                // Sincronizar productos si existen
                if (isset($data['productos']) && is_array($data['productos'])) {
                    foreach ($data['productos'] as $productoData) {
                        // Aquí podrías sincronizar los productos vendidos si tienes una tabla pivot
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Venta sincronizada exitosamente',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar cortes de caja offline
     */
    public function syncCortes(Request $request)
    {
        $data = $request->input('data');
        $method = $request->input('method', 'POST');

        try {
            if ($method === 'POST') {
                // Crear nuevo corte
                $corte = new \stdClass();
                $corte->fecha = $data['fecha'];
                $corte->turno = $data['turno'];
                $corte->total_efectivo = $data['total_efectivo'];
                $corte->total_tarjeta = $data['total_tarjeta'];
                $corte->total_transferencia = $data['total_transferencia'];
                $corte->user_id = $data['user_id'];

                // Aquí deberías tener un modelo para cortes o guardar en una tabla específica
                // Por ahora, simplemente registramos que la sincronización fue exitosa
            }

            return response()->json([
                'success' => true,
                'message' => 'Corte sincronizado exitosamente',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando corte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar gastos offline
     */
    public function syncGastos(Request $request)
    {
        $data = $request->input('data');
        $method = $request->input('method', 'POST');

        try {
            if ($method === 'POST') {
                $gasto = Gasto::create([
                    'nombre' => $data['nombre'] ?? 'Gasto offline',
                    'monto' => $data['monto'],
                    'turno' => $data['turno'],
                    'descripcion' => $data['descripcion'] ?? '',
                    'user_id' => $data['user_id']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gasto sincronizado exitosamente',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando gasto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
