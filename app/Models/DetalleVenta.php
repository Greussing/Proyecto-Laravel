<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Eventos del modelo → manejar stock y movimientos
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        // Cuando se crea un detalle de venta
        static::creating(function (DetalleVenta $detalle) {
            return DB::transaction(function () use ($detalle) {
                $producto = Producto::lockForUpdate()->find($detalle->producto_id);

                if (!$producto) {
                    throw new \Exception('Producto no encontrado.');
                }

                // VALIDACIÓN → no vender más que el stock disponible
                if ($detalle->cantidad > $producto->cantidad) {
                    throw ValidationException::withMessages([
                        'cantidad' => 'No se puede vender una cantidad mayor al stock disponible. Stock actual: ' . $producto->cantidad,
                    ]);
                }

                $stockAntes = $producto->cantidad;
                $producto->cantidad -= $detalle->cantidad;
                
                // Marcar que este cambio NO debe registrarse en historial
                // porque ya se registra como "venta" en VentaService
                $producto->skipHistoryLog = true;
                $producto->save();

                MovimientoStock::create([
                    'producto_id'   => $producto->id,
                    'venta_id'      => $detalle->venta_id,
                    'cliente'       => $detalle->venta->cliente ?? null,
                    'usuario_id'    => auth()->id() ?? 1,
                    'tipo'          => 'venta',
                    'cantidad'      => -$detalle->cantidad,
                    'stock_antes'   => $stockAntes,
                    'stock_despues' => $producto->cantidad,
                    'detalle'       => 'Registro de venta — unidades descontadas del inventario',
                ]);

                return true;
            });
        });

        // Cuando se actualiza un detalle (cambiaste la cantidad)
        static::updating(function (DetalleVenta $detalle) {
            return DB::transaction(function () use ($detalle) {
                $producto = Producto::lockForUpdate()->find($detalle->producto_id);

                if (!$producto) {
                    throw new \Exception('Producto no encontrado.');
                }

                $cantidadOriginal = $detalle->getOriginal('cantidad');
                $nuevaCantidad = $detalle->cantidad;
                $diferencia = $nuevaCantidad - $cantidadOriginal; // puede ser + o -

                // Si se está aumentando la cantidad vendida, validar stock
                if ($diferencia > 0 && $diferencia > $producto->cantidad) {
                    throw ValidationException::withMessages([
                        'cantidad' => 'No hay stock suficiente para aumentar la cantidad. Stock actual: ' . $producto->cantidad,
                    ]);
                }

                $stockAntes = $producto->cantidad;
                // si diferencia es positiva, restamos; si es negativa, sumamos
                $producto->cantidad -= $diferencia;
                
                // Marcar que este cambio NO debe registrarse en historial
                $producto->skipHistoryLog = true;
                $producto->save();

                MovimientoStock::create([
                    'producto_id'   => $producto->id,
                    'venta_id'      => $detalle->venta_id,
                    'cliente'       => $detalle->venta->cliente ?? null,
                    'usuario_id'    => auth()->id() ?? 1,
                    'tipo'          => 'edicion',
                    'cantidad'      => -$diferencia,
                    'stock_antes'   => $stockAntes,
                    'stock_despues' => $producto->cantidad,
                    'detalle' => 'Edición de venta — cantidad modificada (de ' 
                              . $cantidadOriginal . ' a ' . $nuevaCantidad . ')',
                ]);

                return true;
            });
        });

        // Cuando se elimina un detalle (se anula venta o item)
        static::deleting(function (DetalleVenta $detalle) {
            return DB::transaction(function () use ($detalle) {
                $producto = Producto::lockForUpdate()->find($detalle->producto_id);

                if ($producto) {
                    $stockAntes = $producto->cantidad;
                    $producto->cantidad += $detalle->cantidad;
                    
                    // Marcar que este cambio NO debe registrarse en historial
                    $producto->skipHistoryLog = true;
                    $producto->save();

                    // Registramos movimiento en movimientos_stock, no en historial
                    MovimientoStock::create([
                        'producto_id'   => $producto->id,
                        'venta_id'      => $detalle->venta_id,
                        'cliente'       => $detalle->venta->cliente ?? null,
                        'usuario_id'    => auth()->id() ?? 1,
                        'tipo'          => 'anulacion',
                        'cantidad'      => $detalle->cantidad,
                        'stock_antes'   => $stockAntes,
                        'stock_despues' => $producto->cantidad,
                        'detalle'       => 'Eliminación de ítem — unidades retiradas de la venta y devueltas al inventario',
                    ]);
                }

                return true;
            });
        });
    }
}