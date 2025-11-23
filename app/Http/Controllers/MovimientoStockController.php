<?php

namespace App\Http\Controllers;

use App\Models\MovimientoStock;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\MovimientoStockExport;
use App\Services\ExportService;

class MovimientoStockController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
        $this->authorizeResource(MovimientoStock::class, 'movimiento');
    }

    /**
     * INDEX → listado de movimientos
     */
    public function index(Request $request)
    {
        $query = MovimientoStock::with(['producto', 'clienteRelacion', 'usuario'])
            ->buscar($request->input('search'))
            ->filtrarPorTipo($request->input('tipo'))
            ->ordenar($request->input('ordenar'));

        // 🔹 verTodo: true si viene verTodo=1, true, on, etc.
        $verTodo   = $request->boolean('verTodo');
        $porPagina = $verTodo ? $query->count() : 10;

        // Evitar 0
        $porPagina = $porPagina > 0 ? $porPagina : 10;

        $movimientos = $query->paginate($porPagina)->withQueryString();

        return view('movimientos.index', compact('movimientos', 'verTodo'));
    }
    
    /**
     * BUSQUEDA AJAX → autocompletar
     */
    public function busqueda(Request $request)
    {
        $search = $request->get('search');

        $movimientos = MovimientoStock::with(['clienteRelacion', 'usuario', 'producto'])
            ->buscar($search)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json($movimientos);
    }

    /**
     * EXPORTAR PDF
     */
    public function exportPdf(Request $request)
    {
        $movimientos = MovimientoStock::with(['producto', 'clienteRelacion'])
            ->buscar($request->input('search'))
            ->filtrarPorTipo($request->input('tipo'))
            ->ordenar($request->input('ordenar'))
            ->get();

        return $this->exportService->downloadPdf(
            'movimientos.pdf',
            ['movimientos' => $movimientos],
            'movimientos_stock_' . now()->format('Ymd_His'),
            'a4',
            'landscape'
        );
    }

    /**
     * EXPORTAR EXCEL
     */
    public function exportExcel(Request $request)
    {
        return $this->exportService->downloadExcel(
            new MovimientoStockExport($request->all()),
            'movimientos_stock_' . now()->format('Ymd_His')
        );
    }
}