<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnalisisProductosExport;
use Carbon\Carbon;

class AnalisisProductosController extends Controller
{
    /**
     * Construye las estadísticas de análisis de productos
     * para un período (por defecto, últimos 30 días).
     */
    protected function buildStats(int $dias = 30)
    {
        $hasta = Carbon::now();
        $desde = (clone $hasta)->subDays($dias);

        // Query base: productos + detalle_ventas + ventas (últimos X días)
        $productos = Producto::query()
            ->leftJoin('detalle_ventas', 'productos.id', '=', 'detalle_ventas.producto_id')
            ->leftJoin('ventas', function ($join) use ($desde, $hasta) {
                $join->on('detalle_ventas.venta_id', '=', 'ventas.id')
                     ->whereBetween('ventas.fecha', [$desde, $hasta]);
            })
            ->groupBy('productos.id', 'productos.nombre', 'productos.cantidad')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.cantidad as stock_actual',
                DB::raw('COALESCE(SUM(detalle_ventas.cantidad), 0) as total_vendido'),
                DB::raw('COALESCE(SUM(detalle_ventas.subtotal), 0) as ingreso_total'),
                DB::raw('MAX(ventas.fecha) as ultima_venta')
            )
            ->get();

        $ingresoTotalGlobal = $productos->sum('ingreso_total');

        $stats = $productos->map(function ($p) use ($ingresoTotalGlobal, $hasta) {
            $ultima = $p->ultima_venta ? Carbon::parse($p->ultima_venta) : null;
            $diasSinVenta = $ultima ? $ultima->diffInDays($hasta) : null;

            $porcentajeIngresos = $ingresoTotalGlobal > 0
                ? round(($p->ingreso_total / $ingresoTotalGlobal) * 100, 2)
                : 0;

            // Rotación según unidades vendidas en el período
            $vendido = (int) $p->total_vendido;

            if ($vendido === 0) {
                $rotacion = 'Nula';
            } elseif ($vendido <= 9) {
                $rotacion = 'Baja';
            } elseif ($vendido <= 20) {
                $rotacion = 'Media';
            } else {
                $rotacion = 'Alta';
            }

            return (object) [
                'producto'            => $p->nombre,
                'vendido'             => $vendido,
                'ingreso_total'       => (float) $p->ingreso_total,
                'porcentaje_ingresos' => $porcentajeIngresos,
                'stock_actual'        => (int) $p->stock_actual,
                'rotacion'            => $rotacion,
                'dias_sin_venta'      => $diasSinVenta,
                'ultima_venta'        => $ultima,
            ];
        });

        // Orden por ingreso total descendente
        $stats = $stats->sortByDesc('ingreso_total')->values();

        return [$stats, $ingresoTotalGlobal, $desde, $hasta, $dias];
    }

    /**
     * Vista HTML (tabla).
     */
    public function index(Request $request)
    {
        // Podrías permitir cambiar el período (ej: ?dias=60)
        $dias = (int) $request->input('dias', 30);

        [$stats, $ingresoTotalGlobal, $desde, $hasta, $diasPeriodo] = $this->buildStats($dias);

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

        [$stats, $ingresoTotalGlobal, $desde, $hasta, $diasPeriodo] = $this->buildStats($dias);

        $pdf = Pdf::loadView('analisis-productos.pdf', [
                'stats'              => $stats,
                'ingresoTotalGlobal' => $ingresoTotalGlobal,
                'desde'              => $desde,
                'hasta'              => $hasta,
                'diasPeriodo'        => $diasPeriodo,
            ])
            ->setPaper('a4', 'landscape');

        return $pdf->download('analisis_productos_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Exportar a Excel.
     */
    public function exportExcel(Request $request)
    {
        $dias = (int) $request->input('dias', 30);

        [$stats, $ingresoTotalGlobal, $desde, $hasta, $diasPeriodo] = $this->buildStats($dias);

        return Excel::download(
            new AnalisisProductosExport($stats, $desde, $hasta, $diasPeriodo),
            'analisis_productos_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}