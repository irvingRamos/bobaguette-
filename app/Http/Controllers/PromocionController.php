<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use App\Models\Producto;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index()
    {
        // Cualquier usuario autenticado puede ver las promociones
        return view('promociones', [
            'promocionesActivas' => Promocion::where('tipo', '!=', 'del_dia')
                                             ->where('activa', true)->get(),
            'promocionesDia'     => Promocion::where('tipo', 'del_dia')
                                             ->where('activa', true)->get(),
            'productos'          => Producto::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'required|string|max:255',
            'descuento'    => 'required|numeric|min:1',
            'tipo'         => 'required|string',
            'producto1_id' => 'nullable|exists:productos,id',
            'producto2_id' => 'nullable|exists:productos,id',
        ]);

        Promocion::create([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'descuento'    => $request->descuento,
            'tipo'         => $request->tipo,
            'activa'       => $request->has('activa'),
            'producto1_id' => $request->producto1_id,
            'producto2_id' => $request->producto2_id,
        ]);

        return redirect()->route('promociones.index')
                         ->with('success', 'Promoción creada correctamente');
    }

    public function update(Request $request, Promocion $promocion)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'required|string|max:255',
            'descuento'    => 'required|numeric|min:1',
            'tipo'         => 'required|string',
            'producto1_id' => 'nullable|exists:productos,id',
            'producto2_id' => 'nullable|exists:productos,id',
        ]);

        $promocion->update([
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'descuento'    => $request->descuento,
            'tipo'         => $request->tipo,
            'activa'       => $request->has('activa'),
            'producto1_id' => $request->producto1_id,
            'producto2_id' => $request->producto2_id,
        ]);

        return redirect()->route('promociones.index')
                         ->with('success', 'Promoción actualizada correctamente');
    }

    public function destroy(Promocion $promocion)
    {
        $promocion->delete();

        return redirect()->route('promociones.index')
                         ->with('eliminar', 'Promoción eliminada');
    }
}