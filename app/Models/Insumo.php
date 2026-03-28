<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de manera masiva.
     * Estos deben coincidir con los nombres de los inputs en tu formulario y las columnas en la BD.
     */
    protected $fillable = [
        'nombre',
        'categoria',
        'cantidad',
        'unidad',
        'nivel_minimo',
    ];

    /**
     * Opcional: Si quieres que Laravel maneje automáticamente 
     * los tipos de datos al recuperarlos.
     */
    protected $casts = [
        'cantidad' => 'integer',
        'nivel_minimo' => 'integer',
    ];
}