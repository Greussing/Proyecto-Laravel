<?php

// Importamos los controladores y clases necesarias
use App\Http\Controllers\ProductoController;   // Controlador para manejar productos
use App\Http\Controllers\ProfileController;    // Controlador para manejar el perfil del usuario
use Illuminate\Support\Facades\Route;          // Clase de Laravel para definir rutas

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Este archivo define todas las rutas accesibles desde el navegador.
| Cada ruta conecta con una vista o con un controlador.
| Estas rutas se cargan automáticamente por el RouteServiceProvider.
*/

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

//Productos → ProductoController (CRUD: index, create, store, edit, update, destroy)App\Http\Controllers\ProductoController.php + vistas resources/views/productos/*
    Route::resource('productos', ProductoController::class);
});

// Auth → auth.php (login/registro/logout) routes/auth.php (rutas generadas por Laravel Breeze/Jetstream)
require __DIR__.'/auth.php';