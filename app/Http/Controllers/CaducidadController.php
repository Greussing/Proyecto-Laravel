<?php

namespace App\Http\Controllers;

use App\Exports\CaducidadProductosExport;
use App\Services\CaducidadService;
use App\Services\ExportService;
use Illuminate\Http\Request;

class CaducidadController extends Controller
{
    public function __construct(
        protected CaducidadService $caducidadService,
        protected ExportService $exportService
    ) {}

    public function index()
    {
        [$proximos, $vencidos, $revision] = $this->caducidadService->getReporteCaducidad();

        return view('caducidad.index', compact('proximos', 'vencidos', 'revision'));
    }

    public function exportPdf()
    {
        [$proximos, $vencidos, $revision] = $this->caducidadService->getReporteCaducidad();

        return $this->exportService->downloadPdf(
            'caducidad.pdf',
            compact('proximos', 'vencidos', 'revision'),
            'reporte_caducidad_productos.pdf'
        );
    }

    public function exportExcel()
    {
        [$proximos, $vencidos, $revision] = $this->caducidadService->getReporteCaducidad();

        return $this->exportService->downloadExcel(
            new CaducidadProductosExport($proximos, $vencidos, $revision),
            'caducidad_productos.xlsx'
        );
    }
}