<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insumo;

class InventarioController extends Controller
{
    /**
     * Muestra la lista de insumos.
     */
    public function index()
    {
        $insumos = Insumo::all();
        return view('inventario', compact('insumos'));
    }

    /**
     * Guarda un nuevo insumo y dispara el aviso verde.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'categoria'    => 'required|string',
            'cantidad'     => 'required|integer|min:0',
            'unidad'       => 'required|string',
            'nivel_minimo' => 'required|integer|min:0',
        ]);

        Insumo::create($request->all());

        // El 'with' asegura que el letrero verde salte al redireccionar
        return redirect()->route('inventario.index')->with('success', 'Insumo agregado con éxito');
    }

    /**
     * Actualiza el stock y avisa que se hizo el cambio.
     */
    public function updateStock(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
        $mensaje = '';
        
        if ($request->action === 'sumar') {
            $insumo->increment('cantidad');
            $mensaje = 'Stock actualizado (+)';
        } elseif ($request->action === 'restar' && $insumo->cantidad > 0) {
            $insumo->decrement('cantidad');
            $mensaje = 'Stock actualizado (-)';
        }

        if ($insumo->fresh()->cantidad <= $insumo->nivel_minimo) {
            return redirect()->back()->with('warning', '¡Atención! ' . $insumo->nombre . ' está en números rojos');
        }

        return redirect()->back()->with('success', $mensaje);
    }

    /**
     * Elimina el producto y dispara el aviso rojo (eliminar).
     */
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->delete();

        // Usamos 'eliminar' para que la vista sepa que debe saltar el letrero rojo
        return redirect()->route('inventario.index')->with('eliminar', 'Insumo eliminado correctamente');
    }

    /**
     * Sincronizar insumos offline
     */
    public function syncInsumos(Request $request)
    {
        $data = $request->input('data');
        $method = $request->input('method', 'POST');

        try {
            if ($method === 'POST') {
                // Crear nuevo insumo
                $insumo = Insumo::create([
                    'nombre' => $data['nombre'],
                    'categoria' => $data['categoria'],
                    'cantidad' => $data['cantidad'],
                    'unidad' => $data['unidad'],
                    'nivel_minimo' => $data['nivel_minimo'] ?? 0
                ]);
            } else if ($method === 'PUT' || $method === 'PATCH') {
                // Actualizar insumo existente
                $insumo = Insumo::find($data['id']);
                if ($insumo) {
                    $insumo->update([
                        'nombre' => $data['nombre'],
                        'categoria' => $data['categoria'],
                        'cantidad' => $data['cantidad'],
                        'unidad' => $data['unidad'],
                        'nivel_minimo' => $data['nivel_minimo'] ?? 0
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Insumo sincronizado exitosamente',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sincronizando insumo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
