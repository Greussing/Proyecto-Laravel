<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriasExport;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Categoria::withCount('productos')
            ->with(['productos' => function($q) {
                $q->select('id', 'categoria', 'precio', 'cantidad');
            }]);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nombre', 'LIKE', "%{$search}%");
        }

        // Ordenamiento
        $ordenar = $request->get('ordenar', 'nombre_asc');
        switch ($ordenar) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'productos_mas':
                $query->orderBy('productos_count', 'desc');
                break;
            case 'productos_menos':
                $query->orderBy('productos_count', 'asc');
                break;
        }

        $categorias = $query->paginate(10)->appends($request->except('page'));
        
        // Calcular métricas para cada categoría
        $categorias->getCollection()->transform(function ($categoria) {
            // Valor total del inventario (precio × stock)
            $categoria->valor_inventario = $categoria->productos->sum(function ($producto) {
                return $producto->precio * $producto->cantidad;
            });
            
            // Productos con stock crítico (≤ 5)
            $categoria->stock_critico_count = $categoria->productos->filter(function ($producto) {
                return $producto->cantidad <= 5;
            })->count();
            
            return $categoria;
        });

        // Calcular resumen general
        $totalCategorias = Categoria::count();
        $categoriaConMasProductos = Categoria::withCount('productos')
            ->orderBy('productos_count', 'desc')
            ->first();
        
        // Valor total del inventario global
        $valorTotalInventario = Categoria::with('productos')->get()->sum(function ($categoria) {
            return $categoria->productos->sum(function ($producto) {
                return $producto->precio * $producto->cantidad;
            });
        });

        return view('categorias.index', compact(
            'categorias',
            'totalCategorias',
            'categoriaConMasProductos',
            'valorTotalInventario'
        ));
    }

    /**
     * Búsqueda AJAX para tiempo real
     */
    public function busqueda(Request $request)
    {
        $search = $request->get('search', '');
        
        $categorias = Categoria::withCount('productos')
            ->with(['productos' => function($q) {
                $q->select('id', 'categoria', 'precio', 'cantidad');
            }])
            ->where('nombre', 'LIKE', "%{$search}%")
            ->orderBy('nombre', 'asc')
            ->get();

        // Calcular métricas para cada categoría
        $categorias->transform(function ($categoria) {
            $categoria->valor_inventario = $categoria->productos->sum(function ($producto) {
                return $producto->precio * $producto->cantidad;
            });
            
            $categoria->stock_critico_count = $categoria->productos->filter(function ($producto) {
                return $producto->cantidad <= 5;
            })->count();
            
            return $categoria;
        });

        return response()->json($categorias);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
        ]);

        Categoria::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
        ]);

        $categoria->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categoria $categoria)
    {
        // Verificar si tiene productos asociados
        if ($categoria->productos()->count() > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    /**
     * Export PDF
     */
    public function exportPdf(Request $request)
    {
        $query = Categoria::withCount('productos')
            ->with(['productos' => function($q) {
                $q->select('id', 'categoria', 'precio', 'cantidad');
            }]);

        if ($request->filled('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%");
        }

        switch ($request->get('ordenar', 'nombre_asc')) {
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'productos_mas':
                $query->orderBy('productos_count', 'desc');
                break;
            case 'productos_menos':
                $query->orderBy('productos_count', 'asc');
                break;
            default:
                $query->orderBy('nombre', 'asc');
        }

        $categorias = $query->get();

        $pdf = Pdf::loadView('categorias.pdf', compact('categorias'));
        return $pdf->download('Categorias.pdf');
    }

    /**
     * Export Excel
     */
    public function exportExcel()
    {
        return Excel::download(new CategoriasExport, 'Categorias.xlsx');
    }
}
