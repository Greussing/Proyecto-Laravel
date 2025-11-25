<?php

namespace App\Services;

use App\Enums\VentaEstado;
use App\Models\DetalleVenta;
use App\Models\Historial;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Crear una nueva venta.
     */
    public function createVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $producto = Producto::findOrFail($data['producto']);
            $cantidad = (int) $data['cantidad'];

            // Normalizar precio
            $precioUnitario = $this->normalizarPrecio($data['precio_unitario']);

            // Validar stock (redundante con DetalleVenta observer, pero buena práctica aquí también antes de crear nada)
            if ($producto->cantidad < $cantidad) {
                throw new \Exception("Stock insuficiente para {$producto->nombre}");
            }

            $total = $precioUnitario * $cantidad;

            // Crear venta
            $venta = Venta::create([
    'cliente'      => $data['cliente'],
    'usuario'      => auth()->id(),
    'total'        => $total,
    'metodo_pago'  => $data['metodo_pago'],
    'estado'       => $data['estado'],
    'fecha'        => now(), 
            ]);
            // Crear detalle (esto dispara los observers de DetalleVenta que actualizan stock)
            DetalleVenta::create([
                'venta_id'        => $venta->id,
                'producto_id'     => $producto->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $total,
            ]);

            // Registrar en historial (esto estaba en el controlador original)
            Historial::create([
                'producto_id' => $producto->id,
                'accion'      => 'venta',
                'descripcion' => "Se vendieron {$cantidad} unidades de '{$producto->nombre}'.",
            ]);

            return $venta;
        });
    }

    /**
     * Actualizar una venta existente.
     */
    public function updateVenta(Venta $venta, array $data): Venta
    {
        return DB::transaction(function () use ($venta, $data) {
            $detalle = $venta->detalles()->first();

            if (!$detalle) {
                throw new \Exception('La venta no tiene detalle asociado.');
            }

            $precioUnitario = $this->normalizarPrecio($data['precio_unitario']);
            $productoNuevo  = Producto::findOrFail($data['producto']);
            $cantidadNueva  = (int) $data['cantidad'];
            $subtotalNuevo  = $precioUnitario * $cantidadNueva;

            // Lógica de cambio de producto vs mismo producto
            if ($detalle->producto_id == $productoNuevo->id) {
                // Mismo producto: actualizar detalle (dispara observer updating)
                $detalle->cantidad        = $cantidadNueva;
                $detalle->precio_unitario = $precioUnitario;
                $detalle->subtotal        = $subtotalNuevo;
                $detalle->save();
            } else {
                // Producto diferente: borrar y crear (dispara observers deleting y creating)
                $detalle->delete();

                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $productoNuevo->id,
                    'cantidad'        => $cantidadNueva,
                    'precio_unitario' => $precioUnitario,
                    'subtotal'        => $subtotalNuevo,
                ]);
            }

            // Actualizar venta
            $venta->update([
    'cliente'      => $data['cliente'],
    'total'        => $subtotalNuevo,
    'metodo_pago'  => $data['metodo_pago'],
    'estado'       => $data['estado'],
    'fecha'        => now(),
]);
            // Historial
            Historial::create([
                'producto_id' => $productoNuevo->id,
                'accion'      => 'venta actualizada',
                'descripcion' => "Se actualizó la venta #{$venta->id} ({$cantidadNueva} unidad(es) de '{$productoNuevo->nombre}').",
            ]);

            return $venta;
        });
    }

    /**
     * Eliminar una venta.
     */
    public function deleteVenta(Venta $venta): void
    {
        DB::transaction(function () use ($venta) {
            // Eliminar detalles dispara observer deleting (revertir stock)
            foreach ($venta->detalles as $detalle) {
                $detalle->delete();
            }
            $venta->delete();
        });
    }

    /**
     * Procesar devolución.
     */
    public function procesarDevolucion(Venta $venta, int $cantidad, ?string $detalle): void
    {
        $this->stockService->registrarDevolucion($venta, $cantidad, $detalle);
    }

    /**
     * Helper para normalizar precio.
     */
    protected function normalizarPrecio(string|float|int $precio): int
{
    // Convertir a string siempre
    $precio = (string) $precio;

    // Quitar cualquier caracter NO numérico
    $precio = preg_replace('/\D/', '', $precio);

    return (int) $precio;
}
}
