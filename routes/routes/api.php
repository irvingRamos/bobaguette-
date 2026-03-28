<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Importamos el controlador que acabas de crear
use App\Http\Controllers\Api\ProductoApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Esta línea registra automáticamente las 5 rutas necesarias para el CRUD
Route::apiResource('productos', ProductoApiController::class);