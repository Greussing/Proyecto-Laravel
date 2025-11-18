<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CaducidadProductosExport;

    /**
     * Muestra resumen de caducidad:
     * - Próximos a vencer (< 30 días)
     * - Vencidos
     * - (Opcional) Revisión (30–60 días)
     */
    class CaducidadController extends Controller
{
    public function index(Request $request)
    {
        $hoy     = now()->startOfDay();
        $hasta30 = $hoy->copy()->addDays(30);
        $hasta60 = $hoy->copy()->addDays(60);

        $baseQuery = Producto::whereNotNull('fecha_vencimiento');

        $proximos = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $hasta30)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $vencidos = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $revision = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>', $hasta30)
            ->whereDate('fecha_vencimiento', '<=', $hasta60)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return view('caducidad.index', compact('proximos', 'vencidos', 'revision'));
    }

    public function exportPdf()
    {
        $hoy     = now()->startOfDay();
        $hasta30 = $hoy->copy()->addDays(30);
        $hasta60 = $hoy->copy()->addDays(60);

        $baseQuery = Producto::whereNotNull('fecha_vencimiento');

        $proximos = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $hasta30)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $vencidos = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $revision = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>', $hasta30)
            ->whereDate('fecha_vencimiento', '<=', $hasta60)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $pdf = Pdf::loadView('caducidad.pdf', compact('proximos', 'vencidos', 'revision'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('reporte_caducidad_productos.pdf');
    }

    public function exportExcel()
    {
        $hoy     = now()->startOfDay();
        $hasta30 = $hoy->copy()->addDays(30);
        $hasta60 = $hoy->copy()->addDays(60);

        $baseQuery = Producto::whereNotNull('fecha_vencimiento');

        $proximos = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>=', $hoy)
            ->whereDate('fecha_vencimiento', '<=', $hasta30)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $vencidos = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        $revision = (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>', $hasta30)
            ->whereDate('fecha_vencimiento', '<=', $hasta60)
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        return Excel::download(
            new CaducidadProductosExport($proximos, $vencidos, $revision),
            'caducidad_productos.xlsx'
        );
    }
}