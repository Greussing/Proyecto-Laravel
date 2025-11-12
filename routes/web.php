<?php

// Importamos los controladores y clases necesarias
use App\Http\Controllers\ProductoController;   // Controlador para manejar productos
use App\Http\Controllers\ProfileController;    // Controlador para manejar perfiles de usuario
use App\Http\Controllers\HistorialController;  // Controlador para manejar historial de productos  
use App\Http\Controllers\VentaController;        // Controlador para manejar ventas
use Illuminate\Support\Facades\Route;          // Clase de Laravel para definir rutas

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Este archivo define todas las rutas accesibles desde el navegador.
| Cada ruta conecta con una vista o con un controlador.
| Estas rutas se cargan automáticamente por el RouteServiceProvider.
*/

// Ventas → VentaController (CRUD: index, create, store, show, edit, update, destroy)App\Http\Controllers\VentaController.php
Route::resource('ventas', VentaController::class);

// Historial → HistorialController (vista, exportar PDF/Excel)App\Http\Controllers\HistorialController.php
Route::get('/historial', [HistorialController::class, 'index'])->name('historial.index');
Route::get('/historial/export/pdf', [HistorialController::class, 'exportPdf'])->name('historial.export.pdf');
Route::get('/historial/export/excel', [HistorialController::class, 'exportExcel'])->name('historial.export.excel');

// Inicio → Welcome (view)resources/views/welcome.blade.php
Route::get('/', function () {
    return view('welcome');
});

// Panel → Dashboard (view)resources/views/dashboard.blade.php (solo usuarios logueados/verificados)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Protegido → Requiere login
Route::middleware('auth')->group(function () {
    // Perfil → ProfileController (editar, actualizar, eliminar perfil)App\Http\Controllers\ProfileController.php
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Resumen de productos → ProductoController@resumen
Route::get('/productos/resumen', [ProductoController::class, 'resumen'])->name('productos.resumen');

//Productos → ProductoController (CRUD: index, create, store, edit, update, destroy)App\Http\Controllers\ProductoController.php + vistas resources/views/productos/*
    Route::get('/productos/busqueda', [App\Http\Controllers\ProductoController::class, 'busqueda'])
    ->name('productos.busqueda');

Route::resource('productos', ProductoController::class);
});

// Auth → auth.php (login/registro/logout) routes/auth.php (rutas generadas por Laravel Breeze/Jetstream)
require __DIR__.'/auth.php';

