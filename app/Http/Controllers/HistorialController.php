<?php

namespace App\Http\Controllers;

use App\Exports\HistorialExport;
use App\Models\Historial;
use App\Services\ExportService;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

   public function index(Request $request)
{
    // 🔹 Solo acciones de productos (NO ventas)
    $query = Historial::with(['producto' => fn($q) => $q->withTrashed(), 'usuario'])
        ->soloProductos()
        ->buscar($request->input('search'))
        ->filtrarPorAccion($request->input('accion'))
        ->ordenar($request->input('ordenar'));

    // 🔁 Ver todo / paginado
    $verTodo   = $request->boolean('verTodo');
    $porPagina = $verTodo ? $query->count() : 15;
    $porPagina = $porPagina > 0 ? $porPagina : 15;

    $historial = $query->paginate($porPagina)->withQueryString();

    return view('historial.index', compact('historial', 'verTodo'));
}
    /*
    |--------------------------------------------------------------------------
    | BUSQUEDA AJAX → SOLO productos (no ventas)
    |--------------------------------------------------------------------------
    */
    public function busqueda(Request $request)
    {
        $historiales = Historial::with(['producto' => fn($q) => $q->withTrashed()])
            ->soloProductos()
            ->buscar($request->input('search'))
            ->ordenar('fecha_desc')
            ->take(50)
            ->get();

        return response()->json($historiales);
    }

    // Descargar PDF
    public function exportPdf(Request $request)
    {
        $historiales = Historial::with(['producto' => fn($q) => $q->withTrashed(), 'usuario'])
            ->soloProductos()
            ->buscar($request->input('search'))
            ->filtrarPorAccion($request->input('accion'))
            ->ordenar($request->input('ordenar'))
            ->get();

        return $this->exportService->downloadPdf(
            'historial.pdf',
            compact('historiales'),
            'HistorialMovimientos.pdf'
        );
    }

    // Descargar Excel
    public function exportExcel()
    {
        return $this->exportService->downloadExcel(new HistorialExport, 'HistorialMovimientos.xlsx');
    }
}