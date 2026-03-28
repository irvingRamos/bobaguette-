<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'descuento',
        'tipo',
        'activa',
        'producto1_id',
        'producto2_id',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function producto1()
    {
        return $this->belongsTo(Producto::class, 'producto1_id');
    }

    public function producto2()
    {
        return $this->belongsTo(Producto::class, 'producto2_id');
    }
}