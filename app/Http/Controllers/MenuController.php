<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Venta;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $productos = Producto::where('activo', true)->get();
        $promociones = Promocion::where('activa', true)
                                ->with(['producto1', 'producto2'])
                                ->get();
        return view('menu', compact('productos', 'promociones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'categoria' => 'required|string',
            'precio'    => 'required|numeric|min:0',
            'imagen'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación de imagen real
            'imagen_url' => 'nullable|string', // URL opcional si no sube archivo
        ]);

        $imagenPath = $request->imagen_url ?? null;

        // Si sube un archivo, lo guardamos en public/productos
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('productos'), $filename);
            $imagenPath = '/productos/' . $filename;
        }

        Producto::create([
            'nombre'    => $request->nombre,
            'categoria' => $request->categoria,
            'precio'    => $request->precio,
            'imagen'    => $imagenPath,
        ]);

        return redirect()->route('menu.index')->with('success', '¡Producto agregado!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'categoria' => 'required|string',
            'precio'    => 'required|numeric|min:0',
            'imagen'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'imagen_url' => 'nullable|string',
        ]);

        $producto = Producto::findOrFail($id);
        $imagenPath = $request->imagen_url ?? $producto->imagen;

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si no era una URL externa y existía
            if ($producto->imagen && strpos($producto->imagen, '/productos/') === 0) {
                $oldPath = public_path($producto->imagen);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('imagen');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('productos'), $filename);
            $imagenPath = '/productos/' . $filename;
        }

        $producto->update([
            'nombre'    => $request->nombre,
            'categoria' => $request->categoria,
            'precio'    => $request->precio,
            'imagen'    => $imagenPath,
        ]);

        return redirect()->route('menu.index')->with('success', '¡Producto actualizado!');
    }

    public function destroy($id)
    {
        Producto::findOrFail($id)->delete();
        return redirect()->route('menu.index')->with('eliminar', 'Producto eliminado.');
    }

    public function procesarPago(Request $request)
    {
        $request->validate([
            'total'       => 'required|numeric|min:0',
            'metodo_pago' => 'required|in:Efectivo,Tarjeta,Transferencia',
        ]);

        $venta = Venta::create([
            'total'       => (float) $request->total,
            'metodo_pago' => $request->metodo_pago,
            'turno'       => auth()->user()->turno ?? 'Matutino',
            'user_id'     => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Pago procesado con éxito!',
                'venta'   => $venta
            ]);
        }

        return redirect()->route('menu.index')->with('success', '¡Pago procesado con éxito! Total: $' . number_format($venta->total, 2));
    }
}