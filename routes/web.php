<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MetricasController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CorteCajaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PromocionController;

Route::get('/', function () { return view('login'); });

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // Heartbeat para mantener la sesión viva
    Route::get('/session-heartbeat', function () {
        return response()->json(['status' => 'alive']);
    })->name('session.heartbeat');

    Route::get('/dashboard', function () { 
        if (auth()->user()->rol === 'Administrador') {
            return view('dashboard'); 
        }
        return view('dashboard_trabajador');
    })->name('dashboard');

    // Menú
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}', [MenuController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');
    Route::post('/menu/pago', [MenuController::class, 'procesarPago'])->name('menu.pago');

    // Inventario (Solo lectura para trabajadores, gestión para Admin)
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');

    // Promociones (Acceso total para trabajadores y Admin)
    Route::get('/promociones', [PromocionController::class, 'index'])->name('promociones.index');
    Route::post('/promociones', [PromocionController::class, 'store'])->name('promociones.store');
    Route::put('/promociones/{promocion}', [PromocionController::class, 'update'])->name('promociones.update');
    Route::delete('/promociones/{promocion}', [PromocionController::class, 'destroy'])->name('promociones.destroy');

    // Rutas protegidas para Administradores
    Route::middleware(['admin'])->group(function () {
        // Inventario (Gestión)
        Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
        Route::patch('/inventario/{id}/stock', [InventarioController::class, 'updateStock'])->name('inventario.updateStock');
        Route::delete('/inventario/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');

        // Usuarios
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // Métricas
        Route::get('/metricas', [MetricasController::class, 'index'])->name('metricas.index');
        
        // Eliminación de gastos (solo admin)
        Route::delete('/corte/gasto/{id}', [CorteCajaController::class, 'destroyGasto'])->name('corte.gasto.destroy');
    });

    // Corte de Caja (Acceso para todos, pero con vista filtrada por rol en el controlador)
    Route::get('/corte', [CorteCajaController::class, 'index'])->name('corte.index');
    Route::post('/corte/gasto', [CorteCajaController::class, 'storeGasto'])->name('corte.gasto');
    
    // Rutas para sincronización offline
    Route::post('/api/sync-ventas', [CorteCajaController::class, 'syncVentas'])->name('api.sync.ventas');
    Route::post('/api/sync-cortes', [CorteCajaController::class, 'syncCortes'])->name('api.sync.cortes');
    Route::post('/api/sync-gastos', [CorteCajaController::class, 'syncGastos'])->name('api.sync.gastos');
    Route::post('/api/sync-insumos', [InventarioController::class, 'syncInsumos'])->name('api.sync.insumos');
    Route::post('/api/check-connection', function () {
        return response()->json(['status' => 'online']);
    })->name('api.check.connection');
    
    // Ruta para obtener estado offline
    Route::get('/api/offline-status', function () {
        return response()->json([
            'isOnline' => true,
            'pendingOperations' => 0,
            'localData' => [
                'ventas' => 0,
                'cortes' => 0,
                'gastos' => 0
            ]
        ]);
    })->name('api.offline.status');
});
