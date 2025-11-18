<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\User;
use App\Models\MovimientoStock;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $admin    = User::where('email', 'admin@example.com')->first();

        if ($clientes->isEmpty()) {
            $this->command?->warn("No hay clientes. No se generaron ventas demo.");
            return;
        }

        if (! $admin) {
            $this->command?->warn("No se encontró el usuario admin@example.com. Ejecutá primero UserSeeder.");
            return;
        }

        // 🔹 Crear 30 ventas demo
        for ($i = 1; $i <= 30; $i++) {
            DB::transaction(function () use ($clientes, $admin) {

                // Buscar un producto con stock > 0 en la BD (siempre fresco)
                $producto = Producto::where('cantidad', '>', 0)
                    ->inRandomOrder()
                    ->lockForUpdate()
                    ->first();

                if (! $producto) {
                    // Si ya no hay productos con stock, no seguimos
                    return;
                }

                // Cliente al azar
                $cliente = $clientes->random();

                // Cantidad máxima según stock actual
                $maxStock = $producto->cantidad;
                $cantidad = rand(1, min(3, $maxStock));  // de 1 a 3, sin pasarse del stock

                $precioUnitario = $producto->precio;
                $total          = $precioUnitario * $cantidad;

                // Crear venta
                $venta = Venta::create([
                    'cliente'     => $cliente->id,
                    'usuario'     => $admin->id, // usuario dueño de la venta
                    'total'       => $total,
                    'metodo_pago' => ['Efectivo', 'Tarjeta', 'Transferencia'][array_rand([0, 1, 2])],
                    'estado'      => ['Pagado', 'Pendiente'][array_rand([0, 1])],
                    'fecha'       => now()->subDays(rand(0, 15)), // últimas 2 semanas
                ]);

                // Crear detalle → DetalleVenta::creating descuenta stock y registra MovimientoStock 'venta'
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'producto_id'     => $producto->id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal'        => $total,
                ]);

                // ❌ No tocamos $producto->cantidad aquí; eso ya lo hace DetalleVenta::creating
            });
        }

        $this->command?->info("✔️ Ventas demo generadas sin exceder stock.");

        /*
        |--------------------------------------------------------------------------
        | Movimientos extra para demostrar los 4 tipos:
        | - venta      → ya se generaron varios arriba
        | - edicion    → modificamos un detalle existente
        | - anulacion  → eliminamos un detalle existente
        | - devolucion → registramos una devolución manual
        |--------------------------------------------------------------------------
        */

        // Tomamos algunos detalles ya existentes
        $detalleParaEdicion    = DetalleVenta::with('producto', 'venta')->inRandomOrder()->first();
        $detalleParaAnulacion  = DetalleVenta::with('producto', 'venta')->inRandomOrder()->first();
        $detalleParaDevolucion = DetalleVenta::with('producto', 'venta')->inRandomOrder()->first();

        // 🟡 1) Ejemplo de EDICIÓN (tipo "edicion")
        if ($detalleParaEdicion && $detalleParaEdicion->producto) {
            DB::transaction(function () use ($detalleParaEdicion) {
                $producto = Producto::lockForUpdate()->find($detalleParaEdicion->producto_id);
                if (! $producto) {
                    return;
                }

                $cantidadOriginal = $detalleParaEdicion->cantidad;
                // Bajamos 1 unidad (mínimo 1) para evitar problemas de stock
                $nuevaCantidad = max(1, $cantidadOriginal - 1);

                $detalleParaEdicion->cantidad = $nuevaCantidad;
                $detalleParaEdicion->subtotal = $nuevaCantidad * $detalleParaEdicion->precio_unitario;
                $detalleParaEdicion->save(); // 👉 dispara DetalleVenta::updating → MovimientoStock 'edicion'
            });

            $this->command?->info("✔️ Movimiento extra de tipo 'edicion' generado.");
        }

        // 🔴 2) Ejemplo de ANULACIÓN (tipo "anulacion") eliminando un detalle
        if ($detalleParaAnulacion && $detalleParaAnulacion->producto) {
            DB::transaction(function () use ($detalleParaAnulacion) {
                $detalleParaAnulacion->delete(); // 👉 dispara DetalleVenta::deleting → MovimientoStock 'anulacion'
            });

            $this->command?->info("✔️ Movimiento extra de tipo 'anulacion' generado.");
        }

        // 🔵 3) Ejemplo de DEVOLUCIÓN (tipo "devolucion") creado manualmente
        if ($detalleParaDevolucion && $detalleParaDevolucion->producto && $detalleParaDevolucion->venta) {
            DB::transaction(function () use ($detalleParaDevolucion, $admin) {

                $producto = Producto::lockForUpdate()->find($detalleParaDevolucion->producto_id);
                if (! $producto) {
                    return;
                }

                // Devolvemos hasta 2 unidades (o toda la cantidad si es menor)
                $cantidadDevuelta = min($detalleParaDevolucion->cantidad, 2);
                $stockAntes       = $producto->cantidad;
                $stockDespues     = $producto->cantidad + $cantidadDevuelta;

                // Actualizamos stock
                $producto->cantidad = $stockDespues;
                $producto->save();

                // Registramos movimiento tipo "devolucion"
                MovimientoStock::create([
                    'producto_id'   => $producto->id,
                    'venta_id'      => $detalleParaDevolucion->venta_id,
                    'cliente'       => $detalleParaDevolucion->venta->cliente ?? null,
                    'usuario_id'    => $detalleParaDevolucion->venta->usuario ?? $admin->id,
                    'tipo'          => 'devolucion',
                    'cantidad'      => $cantidadDevuelta,
                    'stock_antes'   => $stockAntes,
                    'stock_despues' => $stockDespues,
                    'detalle'       => 'Devolución aplicada — cantidades devueltas al inventario',
                ]);
            });

            $this->command?->info("✔️ Movimiento extra de tipo 'devolucion' generado.");
        }

        $this->command?->info("✔️ VentaSeeder finalizado (ventas + movimientos extra).");
    }
}