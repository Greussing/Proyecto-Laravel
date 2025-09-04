<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        // Precio
        $precioMin = $request->precio_min ? (int) str_replace(['.', ','], '', $request->precio_min) : null;
        $precioMax = $request->precio_max ? (int) str_replace(['.', ','], '', $request->precio_max) : null;

        // Traer todos los productos filtrados y ordenados
        $productosTodos = Producto::with('categoriaRelacion')
            ->when($request->search, function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->search.'%');
            })
            ->when($request->filled('categorias') || $request->filled('categoria'), function ($q) use ($request) {
                $cats = $request->filled('categorias')
                    ? (array) $request->categorias   // ej: ['Ropa','Electrónica']
                    : [$request->categoria];         // ej: 'Ropa'
                $q->whereIn('categoria', $cats);
            })
            ->when($precioMin, function ($q) use ($precioMin) {
                $q->where('precio', '>=', $precioMin);
            })
            ->when($precioMax, function ($q) use ($precioMax) {
                $q->where('precio', '<=', $precioMax);
            })
            // STOCK
            ->when($request->stock, function ($q, $stock) {
                $stock = (array) $stock;

                if (in_array('disponibles', $stock) && !in_array('agotados', $stock)) {
                    $q->where('cantidad', '>', 0);
                }

                if (in_array('agotados', $stock) && !in_array('disponibles', $stock)) {
                    $q->where('cantidad', '=', 0);
                }
            })
            ->when($request->ordenar, function ($q) use ($request) {
                switch ($request->ordenar) {
                    case 'nombre_asc':  $q->orderBy('nombre', 'asc'); break;
                    case 'nombre_desc': $q->orderBy('nombre', 'desc'); break;
                    case 'precio_asc':  $q->orderBy('precio', 'asc'); break;
                    case 'precio_desc': $q->orderBy('precio', 'desc'); break;
                    case 'stock_asc':   $q->orderBy('cantidad', 'asc'); break;
                    case 'stock_desc':  $q->orderBy('cantidad', 'desc'); break;
                    default:            $q->orderBy('id', 'asc'); break;
                }
            }, function ($q) {
                $q->orderBy('id', 'asc');
            })
            ->get();

        // Asignar numeración consecutiva global
        $contador = 1;
        foreach ($productosTodos as $producto) {
            $producto->numero_fijo = $contador++;
        }

        // Paginación
        $pagina = $request->get('page', 1);
        $verTodo = $request->get('verTodo', false);

        if ($verTodo) {
            // Si toca "Ver todo", mostramos todos sin paginar
            $productosPagina = $productosTodos;
            $productos = new LengthAwarePaginator(
                $productosPagina,
                $productosTodos->count(),
                $productosTodos->count(), // todos
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            // Normal → 10 por página
            $porPagina = 10;
            $productosPagina = $productosTodos->forPage($pagina, $porPagina);

            $productos = new LengthAwarePaginator(
                $productosPagina,
                $productosTodos->count(),
                $porPagina,
                $pagina,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // 🔹 Calcular stock y valor de los productos de la página actual
        $pageStockTotal = $productosPagina->sum('cantidad');
        $pageValorTotal = $productosPagina->sum(fn($p) => $p->cantidad * $p->precio);

        $categorias = Categoria::all();
        return response()->json($categorias);

        return view('productos.index', compact(
            'productos',
            'categorias',
            'pageStockTotal',
            'pageValorTotal'
        ));
    }

    public function create()
    {
        $categorias = Categoria::all();
        return view('productos.create', compact('categorias'));
    }

    public function store(ProductoRequest $request)
    {
        $data = $request->validated();
        $data['precio'] = (float) str_replace('.', '', $request->precio);

        $ultimoConsecutivo = Producto::max('consecutivo') ?? 0;
        $data['consecutivo'] = $ultimoConsecutivo + 1;

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado con éxito.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(ProductoRequest $request, Producto $producto)
    {
        $data = $request->validated();
        $data['precio'] = (float) str_replace('.', '', $request->precio);

        $producto->update($data);

        $pagina = $request->input('page', 1);

        return redirect()
            ->route('productos.index', ['page' => $pagina])
            ->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado con éxito.');
    }
}