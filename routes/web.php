<?php

use Illuminate\Support\Facades\Route;

// Controladores
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\MovimientoStockController;
use App\Http\Controllers\AnalisisProductosController;
use App\Http\Controllers\CaducidadController;
use App\Http\Controllers\VentaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Inicio → Welcome
Route::get('/', function () {
    return view('welcome');
});

// Panel → Dashboard (solo usuarios logueados/verificados)
// Panel → Dashboard (solo usuarios logueados/verificados)
Route::get('/dashboard', function () {
    $totalProductos = \App\Models\Producto::count();
    $ventasHoy = \App\Models\Venta::whereDate('created_at', \Carbon\Carbon::today())->sum('total');
    $movimientosHoy = \App\Models\MovimientoStock::whereDate('created_at', \Carbon\Carbon::today())->count();
    $productosCriticos = \App\Models\Producto::whereColumn('cantidad', '<=', 'stock_minimo')->count();

    $ultimasVentas = \App\Models\Venta::with('detalles')->latest()->take(5)->get();
    $ultimosMovimientos = \App\Models\MovimientoStock::with('producto')->latest()->take(5)->get();

    return view('dashboard', compact('totalProductos', 'ventasHoy', 'movimientosHoy', 'productosCriticos', 'ultimasVentas', 'ultimosMovimientos'));
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas (requieren auth)
Route::middleware('auth')->group(function () {

    /*
    |-----------------------------
    | Ventas
    |-----------------------------
    */
    // CRUD de ventas
    Route::resource('ventas', VentaController::class)->except(['show']);

    // Búsqueda de ventas (AJAX)
    Route::get('/ventas/busqueda', [VentaController::class, 'busqueda'])
        ->name('ventas.busqueda');
        
    // Formulario de devolución
    Route::get('/ventas/{venta}/devolucion', [VentaController::class, 'formDevolucion'])
        ->name('ventas.devolucion.form');

    // Registrar devolución
    Route::post('/ventas/{venta}/devolucion', [VentaController::class, 'registrarDevolucion'])
        ->name('ventas.devolucion.store');

        Route::get('/ventas/export/pdf', [VentaController::class, 'exportPdf'])
        ->name('ventas.export.pdf');

    Route::get('/ventas/export/excel', [VentaController::class, 'exportExcel'])
        ->name('ventas.export.excel');


// Caducidad de productos
        Route::get('/caducidad', [CaducidadController::class, 'index'])
        ->name('caducidad.index');

        Route::get('/caducidad/export/pdf', [CaducidadController::class, 'exportPdf'])
        ->name('caducidad.export.pdf');

    Route::get('/caducidad/export/excel', [CaducidadController::class, 'exportExcel'])
        ->name('caducidad.export.excel');
    /*
    |-----------------------------
    | Historial de productos
    |-----------------------------
    */
    Route::get('/historial', [HistorialController::class, 'index'])
        ->name('historial.index');

    Route::get('/historial/export/pdf', [HistorialController::class, 'exportPdf'])
        ->name('historial.export.pdf');

    Route::get('/historial/export/excel', [HistorialController::class, 'exportExcel'])
        ->name('historial.export.excel');

Route::get('/historial/busqueda', [HistorialController::class, 'busqueda'])
        ->name('historial.busqueda');

/*
    |-----------------------------
    | Análisis de productos
    |-----------------------------
    */
Route::get('/analisis-productos', [AnalisisProductosController::class, 'index'])
        ->name('analisis.index');

    Route::get('/analisis-productos/pdf', [AnalisisProductosController::class, 'exportPdf'])
        ->name('analisis.pdf');

    Route::get('/analisis-productos/excel', [AnalisisProductosController::class, 'exportExcel'])
        ->name('analisis.excel');
    /*
    |-----------------------------
    | Movimientos de stock
    |-----------------------------
    */
    Route::get('/movimientos', [MovimientoStockController::class, 'index'])
        ->name('movimientos.index');

    Route::get('/movimientos/export/pdf', [MovimientoStockController::class, 'exportPdf'])
        ->name('movimientos.export.pdf');

    Route::get('/movimientos/export/excel', [MovimientoStockController::class, 'exportExcel'])
        ->name('movimientos.export.excel');

        Route::get('/movimientos/busqueda', [MovimientoStockController::class, 'busqueda'])
        ->name('movimientos.busqueda');
    /*
    |-----------------------------
    | Productos
    |-----------------------------
    */
    Route::get('/productos/resumen', [ProductoController::class, 'resumen'])
        ->name('productos.resumen');
      
    Route::get('/productos/busqueda', [ProductoController::class, 'busqueda'])
        ->name('productos.busqueda');

        Route::get('/productos/export/pdf', [ProductoController::class, 'exportPdf'])
        ->name('productos.export.pdf');

    Route::get('/productos/export/excel', [ProductoController::class, 'exportExcel'])
        ->name('productos.export.excel');


    Route::resource('productos', ProductoController::class);

    /*
    |-----------------------------
    | Perfil de usuario
    |-----------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Auth → login/registro/logout
require __DIR__ . '/auth.php';