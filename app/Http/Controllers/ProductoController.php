<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

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

    // Crear producto
    $producto = Producto::create($data);

    // Cargar relación de categoría para mostrar el nombre
    $producto->load('categoriaRelacion');

    // Formateos y textos
    $precioFormateado = 'Gs. ' . number_format($producto->precio, 0, ',', '.');
    $categoriaNombre  = $producto->categoriaRelacion->nombre ?? 'Sin categoría';
    $cantidadInicial  = $producto->cantidad;

    // 📝 Descripción estilo humano / natural
    $descripcion = "Se agregó el producto '{$producto->nombre}'. "
        . "Pertenece a la categoría {$categoriaNombre}. "
        . "Inicia con {$cantidadInicial} unidades y un precio de {$precioFormateado}.";

    // Registrar en historial
    Historial::create([
        'producto_id' => $producto->id,
        'user_id'     => auth()->id(),
        'accion'      => 'crear',
        'descripcion' => $descripcion,
    ]);

    return redirect()
        ->route('productos.index')
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

    // Valores antes de actualizar
    $antes = $producto->getOriginal();

    // Actualizamos
    $producto->update($data);

    // Relación de categoría ya actualizada
    $producto->load('categoriaRelacion');

    $cambios = [];

    foreach ($data as $campo => $valorNuevo) {
        $valorAnterior = $antes[$campo] ?? null;

        if ($valorAnterior != $valorNuevo) {

            // Formato especial para precio
            if ($campo === 'precio') {
                $valorAnterior = 'Gs. ' . number_format($valorAnterior, 0, ',', '.');
                $valorNuevo    = 'Gs. ' . number_format($valorNuevo, 0, ',', '.');
                $labelCampo    = 'Precio';
            }
            // Formato especial para categoría (mostrar nombres)
            elseif ($campo === 'categoria') {
                $catAntes = Categoria::find($valorAnterior);
                $catNueva = Categoria::find($valorNuevo);

                $valorAnterior = $catAntes->nombre ?? 'Sin categoría';
                $valorNuevo    = $catNueva->nombre ?? 'Sin categoría';
                $labelCampo    = 'Categoría';
            }
            // Otros campos (nombre, cantidad, fecha_vencimiento, lote, etc.)
            else {
                $labelCampo = ucfirst(str_replace('_', ' ', $campo));
            }

            $cambios[] = "{$labelCampo}: {$valorAnterior} → {$valorNuevo}";
        }
    }

    // 📝 Descripción estilo humano / natural
    $descripcion = "Se actualizó el producto '{$producto->nombre}'.";
    if (!empty($cambios)) {
        $descripcion .= " Cambios realizados: " . implode("; ", $cambios);
    }

    // Guardar en historial
    Historial::create([
        'producto_id' => $producto->id,
        'user_id'     => auth()->id(),
        'accion'      => 'editar',
        'descripcion' => $descripcion,
    ]);

    return redirect()
        ->route('productos.index', ['page' => $request->input('page', 1)])
        ->with('success', 'Producto actualizado con éxito.');
}

/*
|--------------------------------------------------------------------------
| DESTROY → eliminar producto
|--------------------------------------------------------------------------
*/
public function destroy(Producto $producto)
{
    // Cargamos categoría antes de borrar para poder registrarla
    $producto->load('categoriaRelacion');

    $categoriaNombre = $producto->categoriaRelacion->nombre ?? 'Sin categoría';

    // 📝 Descripción estilo humano / natural
    $descripcion = "Se eliminó el producto '{$producto->nombre}' "
        . "de la categoría {$categoriaNombre}.";

    Historial::create([
        'producto_id' => $producto->id,
        'user_id'     => auth()->id(),
        'accion'      => 'eliminar',
        'descripcion' => $descripcion,

    ]);

    $producto->delete();

    return redirect()
        ->route('productos.index')
        ->with('success', 'Producto eliminado con éxito.');
}
}