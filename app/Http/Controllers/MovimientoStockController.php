<?php

namespace App\Http\Controllers;

use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MovimientoStockExport;

class MovimientoStockController extends Controller
{
    // LISTADO
    public function index(Request $request)
    {
        $query = MovimientoStock::with(['producto', 'clienteRelacion']);

        // 🔎 Filtro búsqueda (producto, cliente o detalle)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('producto', function ($p) use ($search) {
                    $p->where('nombre', 'like', "%{$search}%");
                })
                ->orWhereHas('clienteRelacion', function ($c) use ($search) {
                    $c->where('nombre', 'like', "%{$search}%");
                })
                ->orWhere('detalle', 'like', "%{$search}%");
            });
        }

        // 🎯 Filtro por tipo
        if ($request->filled('tipo')) {
            $query->whereIn('tipo', (array) $request->tipo);
        }

        // 🔽 Ordenar
        switch ($request->ordenar) {
            case 'fecha_asc':
                $query->orderBy('created_at', 'asc');
                break;

            case 'cantidad_desc':
                $query->orderBy('cantidad', 'desc');
                break;

            case 'cantidad_asc':
                $query->orderBy('cantidad', 'asc');
                break;

            default:
                $query->orderBy('created_at', 'desc'); // recientes primero
                break;
        }

        // 📄 Paginación / Ver todo
        $perPage = $request->has('verTodo') ? 100000 : 20;

        $movimientos = $query->paginate($perPage)->withQueryString();

        return view('movimientos.index', compact('movimientos'));
    }
    
// BÚSQUEDA AJAX
    public function busqueda(Request $request)
{
    $search = $request->get('search');

    $movimientos = MovimientoStock::with(['clienteRelacion', 'usuario', 'producto'])
        ->when($search, function ($q) use ($search) {
            $q->whereHas('producto', function ($sub) use ($search) {
                $sub->where('nombre', 'like', "%{$search}%");
            });
        })
        ->orderByDesc('created_at')
        ->limit(100)
        ->get();

    return response()->json($movimientos);
}
    // PDF
    public function exportPdf(Request $request)
    {
        $query = $this->buildFilteredQuery($request);
        $movimientos = $query->get();

        $pdf = Pdf::loadView('movimientos.pdf', [
            'movimientos' => $movimientos,
        ])->setPaper('A4', 'landscape');

        $fileName = 'movimientos_stock_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }

    // EXCEL
    public function exportExcel(Request $request)
    {
        $fileName = 'movimientos_stock_' . now()->format('Ymd_His') . '.xlsx';

        // Pasamos los filtros actuales al export
        return Excel::download(new MovimientoStockExport($request->all()), $fileName);
    }

    /**
     * Construye la query con los mismos filtros que el index/export usa.
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = MovimientoStock::with('producto', 'venta');

        // Buscador general
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('tipo', 'like', "%$search%")
                  ->orWhere('detalle', 'like', "%$search%")
                  ->orWhereHas('producto', fn($p) =>
                      $p->where('nombre', 'like', "%$search%")
                  );
            });
        }

        // Filtro por tipo (puede venir como string o array)
        if ($request->filled('tipo')) {
            $tipos = (array) $request->tipo;
            $query->whereIn('tipo', $tipos);
        }

        // Ordenar
        if ($request->filled('ordenar')) {
            $orden = $request->ordenar;

            $campo = 'created_at';
            $dir   = 'desc';

            switch ($orden) {
                case 'fecha_asc':
                    $campo = 'created_at';
                    $dir   = 'asc';
                    break;
                case 'fecha_desc':
                    $campo = 'created_at';
                    $dir   = 'desc';
                    break;
                case 'cantidad_asc':
                    $campo = 'cantidad';
                    $dir   = 'asc';
                    break;
                case 'cantidad_desc':
                    $campo = 'cantidad';
                    $dir   = 'desc';
                    break;
            }

            $query->orderBy($campo, $dir);
        } else {
            $query->latest();
        }

        return $query;
    }
}