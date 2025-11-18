<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HistorialExport;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        // 🔹 Solo acciones de productos (NO ventas)
        $query = Historial::with(['producto' => function ($q) {
                $q->withTrashed(); // 🔥 Mantiene productos eliminados visibles
            }])
            ->whereIn('accion', ['crear', 'editar', 'eliminar']);

        // 🔎 Búsqueda por producto
        if ($search = $request->input('search')) {
            $query->whereHas('producto', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            });
        }

        // 🎯 Filtro por acción
        if ($accion = $request->input('accion')) {
            $query->where('accion', $accion);
        }

        // 📎 Ordenar
        switch ($request->input('ordenar')) {
            case 'fecha_asc':
                $query->orderBy('created_at', 'asc');
                break;

            case 'fecha_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

         // 🔁 Ver todo / paginado
    $verTodo = $request->boolean('verTodo');

    if ($verTodo) {
        // usamos como perPage el total de registros
        $perPage = $query->count();
        if ($perPage === 0) {
            $perPage = 1; // evitar perPage = 0
        }
    } else {
        $perPage = 15; // tamaño de página normal
    }

    // SIEMPRE paginator
    $historiales = $query
        ->paginate($perPage)
        ->withQueryString();

    return view('historial.index', compact('historiales', 'verTodo'));
}

    /*
    |--------------------------------------------------------------------------
    | BUSQUEDA AJAX → SOLO productos (no ventas)
    |--------------------------------------------------------------------------
    */
    public function busqueda(Request $request)
    {
        $search = $request->input('search');

        $historiales = Historial::with(['producto' => function ($q) {
                $q->withTrashed(); // 🔥 Mantiene nombres de productos eliminados
            }])
            ->whereIn('accion', ['crear', 'editar', 'eliminar']) // 🔥 Saca ventas DEFINITIVO
            ->when($search, function ($q) use ($search) {
                // Buscar SOLO por nombre del producto
                $q->whereHas('producto', function ($q2) use ($search) {
                    $q2->where('nombre', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json($historiales);
    }

    // Descargar PDF
    public function exportPdf()
{
    $historiales = Historial::with([
            'producto' => function ($q) {
                $q->withTrashed();
            },
            'usuario',
        ])
        ->whereIn('accion', ['crear', 'editar', 'eliminar'])
        ->orderBy('created_at', 'desc')
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('historial.pdf', compact('historiales'));

    return $pdf->download('HistorialMovimientos.pdf');
}

    // Descargar Excel
    public function exportExcel()
    {
        return Excel::download(new HistorialExport, 'HistorialMovimientos.xlsx');
    }
}