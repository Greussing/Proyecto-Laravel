<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX → lista de productos con filtros, orden y paginación
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        // Traer productos filtrados
        $productosTodos = Producto::with('categoriaRelacion')->filter($request)->get();

        // Numeración consecutiva global
        $productosTodos->each(fn ($producto, $i) => $producto->numero_fijo = $i + 1);

        // Paginación y "ver todo"
        $pagina    = $request->get('page', 1);
        $verTodo   = $request->boolean('verTodo');
        $porPagina = $verTodo ? $productosTodos->count() : 10;

        $productosPagina = $productosTodos->forPage($pagina, $porPagina);

        $productos = new LengthAwarePaginator(
            $productosPagina,
            $productosTodos->count(),
            $porPagina,
            $pagina,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Totales de stock y valor de la página actual
        $pageStockTotal = $productosPagina->sum('cantidad');
        $pageValorTotal = $productosPagina->sum(fn($p) => $p->cantidad * $p->precio);

        $categorias = Categoria::all();

        return view('productos.index', compact(
            'productos',
            'categorias',
            'pageStockTotal',
            'pageValorTotal'
        ));
    }
    /*
    |--------------------------------------------------------------------------
    | BUSQUEDA → búsqueda AJAX para autocompletar
    |--------------------------------------------------------------------------
    */
    // app/Http/Controllers/ProductoController.php

public function busqueda(Request $request)
{
    $termino = $request->input('search');

    $productos = Producto::with('categoriaRelacion')
        ->when($termino, fn($q) =>
            $q->where('nombre', 'like', "%{$termino}%")
        )
        ->orderBy('nombre')
        ->take(20)
        ->get();

    return response()->json($productos);
}
    /*
    |--------------------------------------------------------------------------
    | CREATE → formulario de nuevo producto
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $categorias = Categoria::all();
        return view('productos.create', compact('categorias'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE → guardar producto
    |--------------------------------------------------------------------------
    */
public function store(ProductoRequest $request)
{
    $data = $request->validated();
    $producto = Producto::create($data);

    Historial::create([
        'producto_id' => $producto->id,
        'accion' => 'crear',
        'descripcion' => "Se creó el producto '{$producto->nombre}' con precio de Gs. " . number_format($producto->precio, 0, ',', '.') . ".",
    ]);

    return redirect()->route('productos.index')->with('success', 'Producto creado con éxito.');
}
    /*
    |--------------------------------------------------------------------------
    | EDIT → formulario de edición
    |--------------------------------------------------------------------------
    */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        return view('productos.edit', compact('producto', 'categorias'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE → actualizar producto
    |--------------------------------------------------------------------------
    */
    public function update(ProductoRequest $request, Producto $producto)
{
    $data = $request->validated();
    $producto->update($data);

    Historial::create([
        'producto_id' => $producto->id,
        'accion' => 'editar',
        'descripcion' => "Se actualizó el producto '{$producto->nombre}' con precio de Gs. " . number_format($producto->precio, 0, ',', '.') . ".",
    ]);

    return redirect()->route('productos.index', ['page' => $request->input('page', 1)])
        ->with('success', 'Producto actualizado con éxito.');
}

    /*
    |--------------------------------------------------------------------------
    | DESTROY → eliminar producto
    |--------------------------------------------------------------------------
    */
    public function destroy(Producto $producto)
{
    Historial::create([
        'producto_id' => $producto->id,
        'accion' => 'eliminar',
        'descripcion' => "Se eliminó el producto '{$producto->nombre}'.",
    ]);

    $producto->delete();

    return redirect()->route('productos.index')
        ->with('success', 'Producto eliminado con éxito.');
}

public function resumen()
{
    $total = \App\Models\Producto::count();
    $agotados = \App\Models\Producto::where('cantidad', 0)->count();
    $bajoStock = \App\Models\Producto::where('cantidad', '<', 5)->count();
    $promedioPrecio = \App\Models\Producto::avg('precio');

    return response()->json([
        'total_productos' => $total,
        'agotados' => $agotados,
        'bajo_stock' => $bajoStock,
        'promedio_precio' => round($promedioPrecio, 0),
    ]);
}
}