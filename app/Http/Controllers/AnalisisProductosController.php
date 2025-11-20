<?php

namespace App\Http\Controllers;

use App\Exports\AnalisisProductosExport;
use App\Services\AnalisisProductosService;
use App\Services\ExportService;
use Illuminate\Http\Request;

class AnalisisProductosController extends Controller
{
    public function __construct(
        protected AnalisisProductosService $analisisService,
        protected ExportService $exportService
    ) {}

    /**
     * Vista HTML (tabla).
     */
    public function index(Request $request)
    {
        $dias = (int) $request->input('dias', 30);

        [$stats, $ingresoTotalGlobal, $desde, $hasta, $diasPeriodo] = $this->analisisService->getStats($dias);

        return view('analisis-productos.index', [
            'stats'              => $stats,
            'ingresoTotalGlobal' => $ingresoTotalGlobal,
            'desde'              => $desde,
            'hasta'              => $hasta,
            'diasPeriodo'        => $diasPeriodo,
        ]);
    }

    /**
     * Exportar a PDF.
     */
    public function exportPdf(Request $request)
    {
        $dias = (int) $request->input('dias', 30);

        [$stats, $ingresoTotalGlobal, $desde, $hasta, $diasPeriodo] = $this->analisisService->getStats($dias);

        $data = [
            'stats'              => $stats,
            'ingresoTotalGlobal' => $ingresoTotalGlobal,
            'desde'              => $desde,
            'hasta'              => $hasta,
            'diasPeriodo'        => $diasPeriodo,
        ];

        return $this->exportService->downloadPdf(
            'analisis-productos.pdf',
            $data,
            'analisis_productos_' . now()->format('Ymd_His'),
            'a4',
            'landscape'
        );
    }

    /**
     * Exportar a Excel.
     */
    public function exportExcel(Request $request)
    {
        $dias = (int) $request->input('dias', 30);

        [$stats, $ingresoTotalGlobal, $desde, $hasta, $diasPeriodo] = $this->analisisService->getStats($dias);

        $export = new AnalisisProductosExport($stats, $desde, $hasta, $diasPeriodo);

        return $this->exportService->downloadExcel(
            $export,
            'analisis_productos_' . now()->format('Ymd_His')
        );
    }
}