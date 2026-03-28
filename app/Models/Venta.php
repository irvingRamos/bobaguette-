<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'total',
        'metodo_pago',
        'turno',
        'user_id',
        'notas',
    ];

    protected $casts = [
        'total' => 'float',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}