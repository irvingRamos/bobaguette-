<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngresoExtra extends Model
{
    protected $fillable = ['nombre', 'monto', 'turno', 'descripcion'];
}
