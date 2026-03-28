<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Http\Resources\ProductoResource;
use Illuminate\Http\Request;

class ProductoApiController extends Controller
{
    public function index() {
        return ProductoResource::collection(Producto::paginate(10));
    }

    public function show(Producto $producto) {
        return new ProductoResource($producto);
    }

    public function store(Request $request) {
        $request->validate(['nombre'=>'required','precio'=>'required|numeric']);
        $producto = Producto::create($request->all());
        return new ProductoResource($producto);
    }

    public function update(Request $request, Producto $producto) {
        $producto->update($request->all());
        return new ProductoResource($producto);
    }

    public function destroy(Producto $producto) {
        $producto->delete();
        return response()->json(['message' => 'Producto eliminado.']);
    }
}