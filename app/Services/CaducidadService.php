<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;

class CaducidadService
{
    /**
     * Obtiene los productos agrupados por estado de caducidad.
     * Retorna un array con [proximos, vencidos, revision]
     */
    public function getReporteCaducidad(): array
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

        return [$proximos, $vencidos, $revision];
    }
}
