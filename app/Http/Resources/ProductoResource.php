<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nombre'      => $this->nombre,
            'precio'      => number_format($this->precio, 2),
            'stock'       => $this->stock,
            'descripcion' => $this->descripcion,
            'creado_el'   => $this->created_at->format('d/m/Y'),
        ];
    }
}