<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX → lista de ventas
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $ventas = Venta::with(['usuarioRelacion', 'clienteRelacion'])
            ->withCount(['detalles as cantidad_productos' => function ($q) {
                $q->select(\DB::raw('coalesce(sum(cantidad),0)'));
            }])
            ->latest()
            ->paginate(15);

        $pageCantidadVentas = $ventas->count();
        $pageTotalVentas = $ventas->sum('total');

        return view('ventas.index', compact('ventas', 'pageCantidadVentas', 'pageTotalVentas'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE → formulario de nueva venta
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('ventas.create', compact('clientes', 'usuarios', 'productos'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE → guardar venta y actualizar stock
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'cliente'       => 'required|exists:clientes,id',
            'producto'      => 'required|exists:productos,id',
            'cantidad'      => 'required|integer|min:1',
            'precio_unitario' => 'required',
            'metodo_pago'   => 'required|in:Efectivo,Tarjeta,Transferencia',
            'estado'        => 'required|in:Pendiente,Pagado,Anulado',
            'fecha'         => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            // Buscar el producto
            $producto = Producto::findOrFail($request->producto);
            $cantidad = (int) $request->cantidad;

            // Normalizar precio (elimina puntos de miles)
            $precioUnitario = str_replace('.', '', $request->precio_unitario);
            $precioUnitario = (float) str_replace(',', '.', $precioUnitario);

            // Validar stock
            if ($producto->cantidad < $cantidad) {
                throw new \Exception("Stock insuficiente para {$producto->nombre}");
            }

            // Calcular total
            $total = $precioUnitario * $cantidad;

            // Crear venta principal
            $venta = Venta::create([
                'cliente'      => $request->cliente,
                'usuario'      => auth()->id(),
                'total'        => $total,
                'metodo_pago'  => $request->metodo_pago,
                'estado'       => $request->estado,
                'fecha'        => $request->fecha,
            ]);

            // Crear detalle
            DetalleVenta::create([
                'venta_id'        => $venta->id,
                'producto_id'     => $producto->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $total,
            ]);

            // Actualizar stock del producto
            $producto->update([
                'cantidad' => $producto->cantidad - $cantidad,
            ]);

            // Registrar en historial
            Historial::create([
                'producto_id' => $producto->id,
                'accion'      => 'venta',
                'descripcion' => "Se vendieron {$cantidad} unidades de '{$producto->nombre}'.",
            ]);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta registrada con éxito.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT → formulario de edición de venta
    |--------------------------------------------------------------------------
    */
    public function edit(Venta $venta)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $productos = Producto::orderBy('nombre')->get();

        // Obtener el primer detalle (suponiendo 1 producto por venta)
        $detalle = $venta->detalles->first();

        return view('ventas.edit', compact('venta', 'clientes', 'usuarios', 'productos', 'detalle'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE → actualizar venta existente
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Venta $venta)
    {
        $request->validate([
            'cliente'       => 'required|exists:clientes,id',
            'producto'      => 'required|exists:productos,id',
            'cantidad'      => 'required|integer|min:1',
            'precio_unitario' => 'required',
            'metodo_pago'   => 'required|in:Efectivo,Tarjeta,Transferencia',
            'estado'        => 'required|in:Pendiente,Pagado,Anulado',
            'fecha'         => 'required|date',
        ]);

        DB::transaction(function () use ($request, $venta) {
            // Revertir stock anterior (por si cambió algo)
            $detalleAnterior = $venta->detalles->first();
            if ($detalleAnterior) {
                $productoAnterior = Producto::find($detalleAnterior->producto_id);
                if ($productoAnterior) {
                    $productoAnterior->update([
                        'cantidad' => $productoAnterior->cantidad + $detalleAnterior->cantidad,
                    ]);
                }
                $detalleAnterior->delete();
            }

            // Normalizar precio (elimina puntos de miles)
            $precioUnitario = str_replace('.', '', $request->precio_unitario);
            $precioUnitario = (float) str_replace(',', '.', $precioUnitario);

            // Obtener nuevo producto
            $producto = Producto::findOrFail($request->producto);
            $cantidad = (int) $request->cantidad;

            // Validar stock
            if ($producto->cantidad < $cantidad) {
                throw new \Exception("Stock insuficiente para {$producto->nombre}");
            }

            $total = $precioUnitario * $cantidad;

            // Actualizar venta
            $venta->update([
                'cliente'      => $request->cliente,
                'total'        => $total,
                'metodo_pago'  => $request->metodo_pago,
                'estado'       => $request->estado,
                'fecha'        => $request->fecha,
            ]);

            // Crear nuevo detalle
            DetalleVenta::create([
                'venta_id'        => $venta->id,
                'producto_id'     => $producto->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $total,
            ]);

            // Actualizar stock
            $producto->update([
                'cantidad' => $producto->cantidad - $cantidad,
            ]);

            // Registrar en historial
            Historial::create([
                'producto_id' => $producto->id,
                'accion'      => 'venta actualizada',
                'descripcion' => "Se modificó la venta: {$cantidad} unidades de '{$producto->nombre}'.",
            ]);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada con éxito.');
    }

     /*
    |--------------------------------------------------------------------------
    | DESTROY → eliminar venta y revertir stock (con validación de estado)
    |--------------------------------------------------------------------------
    */
    public function destroy(Venta $venta)
    {
        try {
            // 🔒 Evitar eliminar ventas pagadas o anuladas
            if (in_array($venta->estado, ['Pagado', 'Anulado'])) {
                return redirect()->route('ventas.index')
                    ->with('error', "No puedes eliminar una venta con estado '{$venta->estado}'.");
            }

            DB::transaction(function () use ($venta) {
                // Revertir stock de todos los productos de la venta
                foreach ($venta->detalles as $detalle) {
                    $producto = $detalle->producto;
                    if ($producto) {
                        $producto->update([
                            'cantidad' => $producto->cantidad + $detalle->cantidad,
                        ]);

                        // Registrar en historial
                        Historial::create([
                            'producto_id' => $producto->id,
                            'accion'      => 'venta eliminada',
                            'descripcion' => "Se eliminó una venta y se devolvieron {$detalle->cantidad} unidades de '{$producto->nombre}'.",
                        ]);
                    }
                }

                // Eliminar los detalles primero
                $venta->detalles()->delete();

                // Luego eliminar la venta
                $venta->delete();
            });

            return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('ventas.index')->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }
}