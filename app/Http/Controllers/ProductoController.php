<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
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

        // Asignar consecutivo automático
        $data['consecutivo'] = (Producto::max('consecutivo') ?? 0) + 1;

        Producto::create($data);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado con éxito.');
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
        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado con éxito.');
    }
}