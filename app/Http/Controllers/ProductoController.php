<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
{
    $precioMin = $request->precio_min ? str_replace('.', '', $request->precio_min) : null;
    $precioMax = $request->precio_max ? str_replace('.', '', $request->precio_max) : null;

    // Traer todos los productos filtrados y ordenados
    $productosTodos = Producto::with('categoriaRelacion')
        ->when($request->search, function ($q) use ($request) {
            $q->where('nombre', 'like', '%'.$request->search.'%');
        })
        ->when($request->categoria, function ($q) use ($request) {
            $q->where('categoria', $request->categoria);
        })
        ->when($precioMin, function ($q) use ($precioMin) {
            $q->where('precio', '>=', $precioMin);
        })
        ->when($precioMax, function ($q) use ($precioMax) {
            $q->where('precio', '<=', $precioMax);
        })
        ->when($request->stock, function ($q, $stock) {
            $stock = (array) $stock;

            // Solo disponibles
            if (in_array('disponibles', $stock) && !in_array('agotados', $stock)) {
                $q->where('cantidad', '>', 0);
            }

            // Solo agotados
            if (in_array('agotados', $stock) && !in_array('disponibles', $stock)) {
                $q->where('cantidad', '=', 0);
            }

            // Si vienen ambos → no se filtra nada (se muestran todos)
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
            // Si no hay ordenar → por defecto ID asc
            $q->orderBy('id', 'asc');
        })
        ->get();

    // Asignar numeración consecutiva global
    $contador = 1;
    foreach ($productosTodos as $producto) {
        $producto->numero_fijo = $contador++;
    }

    // Paginación manual (10 por página)
    $pagina = $request->get('page', 1);
    $porPagina = 10;
    $productos = $productosTodos->forPage($pagina, $porPagina);

    $productos = new \Illuminate\Pagination\LengthAwarePaginator(
        $productos,
        $productosTodos->count(),
        $porPagina,
        $pagina,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $categorias = Categoria::all();

    return view('productos.index', compact('productos', 'categorias'));
}

public function create()
{
    $categorias = Categoria::all(); // para el select de categorías

    return view('productos.create', compact('categorias'));
}

    public function store(ProductoRequest $request)
{
    $data = $request->validated();
    $data['precio'] = (float) str_replace('.', '', $request->precio); // quita puntos

    // 🔹 Obtener último consecutivo y sumarle 1
    $ultimoConsecutivo = Producto::max('consecutivo') ?? 0;
    $data['consecutivo'] = $ultimoConsecutivo + 1;

    Producto::create($data);

    return redirect()->route('productos.index')->with('success', 'Producto creado con éxito.');
}

    public function show(Producto $producto)
    {
        $producto->load('categoriaRelacion');

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(ProductoRequest $request, Producto $producto)
{
    $data = $request->validated();
    $data['precio'] = (float) str_replace('.', '', $request->precio); // quita puntos

    $producto->update($data);

    // Recuperamos la página actual (default 1 si no viene)
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
