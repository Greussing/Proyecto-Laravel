<?php

namespace App\Services;

use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Registra una devolución de productos al stock.
     */
    public function registrarDevolucion(Venta $venta, int $cantidadDevolver, ?string $detalleNota = null): void
    {
        DB::transaction(function () use ($venta, $cantidadDevolver, $detalleNota) {
            // Cargar detalle y producto
            $detalle = $venta->detalles->first(); // Asumimos 1 producto por venta según lógica actual

            if (!$detalle || !$detalle->producto) {
                throw new \Exception('No se encontró el detalle o producto de la venta.');
            }

            $producto = Producto::lockForUpdate()->find($detalle->producto_id);

            if (!$producto) {
                throw new \Exception('Producto no encontrado para devolución.');
            }

            // 1) Actualizar stock del producto (entra mercadería)
            $stockAntes = $producto->cantidad;
            $producto->cantidad += $cantidadDevolver;
            $producto->save();

            // 2) Registrar movimiento de stock tipo "devolucion"
            MovimientoStock::create([
                'producto_id'   => $producto->id,
                'venta_id'      => $venta->id,
                'cliente'       => $venta->cliente,
                'usuario_id'    => auth()->id(),
                'tipo'          => 'devolucion',
                'cantidad'      => $cantidadDevolver,     // positiva
                'stock_antes'   => $stockAntes,
                'stock_despues' => $producto->cantidad,
                'detalle'       => $detalleNota ?? 'Devolución aplicada — cantidades devueltas al inventario',
            ]);

            // 3) (Opcional) marcar venta como Anulada si se devuelve todo
            if ($cantidadDevolver == $detalle->cantidad) {
                $venta->estado = \App\Enums\VentaEstado::ANULADO->value;
                $venta->save();
            }
        });
    }
}
