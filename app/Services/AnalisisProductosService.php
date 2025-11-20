<?php

namespace App\Services;

use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalisisProductosService
{
    /**
     * Construye las estadísticas de análisis de productos
     * para un período (por defecto, últimos 30 días).
     */
    public function getStats(int $dias = 30): array
    {
        $hasta = Carbon::now();
        $desde = (clone $hasta)->subDays($dias);

        // Query base: productos + detalle_ventas + ventas (últimos X días)
        // Usamos Query Builder sobre Eloquent para mantener rendimiento pero más legible
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

            return (object) [
                'producto'            => $p->nombre,
                'vendido'             => (int) $p->total_vendido,
                'ingreso_total'       => (float) $p->ingreso_total,
                'porcentaje_ingresos' => $porcentajeIngresos,
                'stock_actual'        => (int) $p->stock_actual,
                'rotacion'            => $this->calcularRotacion((int) $p->total_vendido),
                'dias_sin_venta'      => $diasSinVenta,
                'ultima_venta'        => $ultima,
            ];
        });

        // Orden por ingreso total descendente
        $stats = $stats->sortByDesc('ingreso_total')->values();

        return [$stats, $ingresoTotalGlobal, $desde, $hasta, $dias];
    }

    protected function calcularRotacion(int $vendido): string
    {
        return match (true) {
            $vendido === 0 => 'Nula',
            $vendido <= 9  => 'Baja',
            $vendido <= 20 => 'Media',
            default        => 'Alta',
        };
    }
}
