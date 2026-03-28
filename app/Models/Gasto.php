<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = ['nombre', 'monto', 'turno', 'descripcion'];
}
