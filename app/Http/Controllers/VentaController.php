<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Historial;
use App\Models\MovimientoStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX → lista de ventas
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
{
    // Empezamos la query base
    $query = Venta::with(['usuarioRelacion', 'clienteRelacion', 'detalles'])
        ->withCount(['detalles as cantidad_productos' => function ($q) {
            $q->select(\DB::raw('coalesce(sum(cantidad),0)'));
        }]);

    /*
    |--------------------------------------------------------------------------
    | Filtro: fechas (desde / hasta)
    |--------------------------------------------------------------------------
    */
    if ($fechaDesde = $request->input('fecha_desde')) {
        $query->whereDate('fecha', '>=', $fechaDesde);
    }

    if ($fechaHasta = $request->input('fecha_hasta')) {
        $query->whereDate('fecha', '<=', $fechaHasta);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtro: método de pago (checkbox múltiple)
    |--------------------------------------------------------------------------
    */
    if ($metodos = $request->input('metodo_pago')) {
        $metodos = (array) $metodos;
        $query->whereIn('metodo_pago', $metodos);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtro: estado (checkbox múltiple)
    |--------------------------------------------------------------------------
    */
    if ($estados = $request->input('estado')) {
        $estados = (array) $estados;
        $query->whereIn('estado', $estados);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtro: total mínimo / máximo
    |--------------------------------------------------------------------------
    */
    $totalMin = $request->input('total_min');
    if ($totalMin !== null && $totalMin !== '') {
        // eliminar puntos, comas, etc.
        $totalMinNum = (int) preg_replace('/[^\d]/', '', $totalMin);
        $query->where('total', '>=', $totalMinNum);
    }

    $totalMax = $request->input('total_max');
    if ($totalMax !== null && $totalMax !== '') {
        $totalMaxNum = (int) preg_replace('/[^\d]/', '', $totalMax);
        $query->where('total', '<=', $totalMaxNum);
    }

    /*
    |--------------------------------------------------------------------------
    | Ordenar por: fecha / total
    |--------------------------------------------------------------------------
    */
    $orden = $request->input('ordenar');

    switch ($orden) {
        case 'fecha_asc':
            $query->orderBy('fecha', 'asc');
            break;
        case 'fecha_desc':
            $query->orderBy('fecha', 'desc');
            break;
        case 'total_asc':
            $query->orderBy('total', 'asc');
            break;
        case 'total_desc':
            $query->orderBy('total', 'desc');
            break;
        default:
            // mismo comportamiento que antes: las más recientes primero
            $query->orderBy('fecha', 'desc');
            break;
    }

     // Ver todo o paginación
    if ($request->has('verTodo')) {
       // Sin paginación
        $ventas = $query->get();
    } else {
        // Paginación normal
        $ventas = $query
            ->paginate(15) // o 20 si querés
            ->withQueryString();
    }

    return view('ventas.index', compact('ventas'));
}
//
    /*
    |--------------------------------------------------------------------------
    | BUSQUEDA → búsqueda AJAX para autocompletar
    |--------------------------------------------------------------------------
    */
public function busqueda(Request $request)
{
    $termino = $request->input('search');

    $ventas = Venta::with([
            'clienteRelacion:id,nombre',
            'detalles.producto:id,nombre',
        ])
        ->when($termino, function ($q) use ($termino) {
            $q->whereHas('clienteRelacion', function ($sub) use ($termino) {
                $sub->where('nombre', 'like', "%{$termino}%");
            });
        })
        ->orderByDesc('fecha')
        ->take(30)
        ->get();

    return response()->json($ventas);
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
        'cliente'         => 'required|exists:clientes,id',
        'producto'        => 'required|exists:productos,id',
        'cantidad'        => 'required|integer|min:1',
        'precio_unitario' => 'required',
        'metodo_pago'     => 'required|in:Efectivo,Tarjeta,Transferencia',
        'estado'          => 'required|in:Pendiente,Pagado,Anulado',
        'fecha'           => 'required|date',
    ]);

    DB::transaction(function () use ($request, $venta) {

        // Detalle actual de la venta (suponiendo 1 producto por venta)
        $detalle = $venta->detalles()->first();

        if (! $detalle) {
            throw new \Exception('La venta no tiene detalle asociado.');
        }

        // Normalizar precio (elimina puntos de miles)
        $precioUnitario = str_replace('.', '', $request->precio_unitario);
        $precioUnitario = (float) str_replace(',', '.', $precioUnitario);

        $productoNuevo   = Producto::findOrFail($request->producto);
        $cantidadNueva   = (int) $request->cantidad;
        $subtotalNuevo   = $precioUnitario * $cantidadNueva;

        /*
        |--------------------------------------------------------------------------
        | CASO 1: Mismo producto → usamos updating (tipo "edicion")
        |--------------------------------------------------------------------------
        */
        if ($detalle->producto_id == $productoNuevo->id) {

            // Ajustar el detalle → dispara DetalleVenta::updating
            $detalle->cantidad        = $cantidadNueva;
            $detalle->precio_unitario = $precioUnitario;
            $detalle->subtotal        = $subtotalNuevo;
            $detalle->save(); // aquí se maneja el stock y MovimientoStock tipo "edicion"

        /*
        |--------------------------------------------------------------------------
        | CASO 2: Cambiaste de producto → anulamos detalle viejo y creamos uno nuevo
        |--------------------------------------------------------------------------
        */
        } else {
            // 2.1 Eliminar detalle viejo → DetalleVenta::deleting devuelve stock + "anulacion"
            $detalle->delete();

            // 2.2 Crear nuevo detalle → DetalleVenta::creating descuenta stock + "venta"
            DetalleVenta::create([
                'venta_id'        => $venta->id,
                'producto_id'     => $productoNuevo->id,
                'cantidad'        => $cantidadNueva,
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $subtotalNuevo,
            ]);
        }

        // 3) Actualizar la venta (total, estado, etc.)
        $venta->update([
            'cliente'      => $request->cliente,
            'total'        => $subtotalNuevo,
            'metodo_pago'  => $request->metodo_pago,
            'estado'       => $request->estado,
            'fecha'        => $request->fecha,
        ]);

        // registrar algo en Historial si querés mantener esa tabla
        Historial::create([
            'producto_id' => $productoNuevo->id,
            'accion'      => 'venta actualizada',
            'descripcion' => "Se actualizó la venta #{$venta->id} ({$cantidadNueva} unidad(es) de '{$productoNuevo->nombre}').",
        ]);
    });

    return redirect()->route('ventas.index')->with('success', 'Venta actualizada con éxito.');
}

    /*
|--------------------------------------------------------------------------
| FORM DEVOLUCIÓN → muestra el formulario
|--------------------------------------------------------------------------
*/
public function formDevolucion(Venta $venta)
{
    // Cargamos detalles y producto para mostrar info
    $venta->load(['clienteRelacion', 'detalles.producto']);

    return view('ventas.devolucion', compact('venta'));
}

/*
|--------------------------------------------------------------------------
| REGISTRAR DEVOLUCIÓN → suma stock y registra movimiento
|--------------------------------------------------------------------------
*/
public function registrarDevolucion(Request $request, Venta $venta)
{
    // Cargamos detalle y producto
    $venta->load('detalles.producto');
    $detalle = $venta->detalles->first();

    if (!$detalle || !$detalle->producto) {
        return redirect()
            ->route('ventas.index')
            ->with('error', 'No se encontró el detalle o producto de la venta.');
    }

    $request->validate([
        'cantidad_devolver' => [
            'required',
            'integer',
            'min:1',
            'max:' . $detalle->cantidad,  // no dejar devolver más de lo vendido
        ],
        'detalle' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $venta, $detalle) {
        $producto = $detalle->producto->lockForUpdate()->find($detalle->producto_id);

        if (!$producto) {
            throw new \Exception('Producto no encontrado para devolución.');
        }

        $cantidadDevolver = (int) $request->cantidad_devolver;

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
            'detalle'       => $request->detalle
                ?? 'Devolución aplicada — cantidades devueltas al inventario',
        ]);

        // 3) (Opcional) marcar venta como Anulada si se devuelve todo
        if ($cantidadDevolver == $detalle->cantidad) {
            $venta->estado = 'Anulado';
            $venta->save();
        }

        // Si quisieras manejar devoluciones parciales en detalle_ventas,
        // podrías restar la cantidad devuelta del detalle:
        // $detalle->cantidad -= $cantidadDevolver;
        // $detalle->save();
    });

    return redirect()
        ->route('movimientos.index')  // o ventas.index, como prefieras
        ->with('success', 'Devolución registrada correctamente.');
}

     /*
    |--------------------------------------------------------------------------
    | DESTROY → eliminar venta y revertir stock (con validación de estado)
    |--------------------------------------------------------------------------
    */
   public function destroy(Venta $venta)
{
    DB::transaction(function () use ($venta) {
        foreach ($venta->detalles as $detalle) {
            $detalle->delete();
        }

        $venta->delete();
    });

    return redirect()
        ->route('ventas.index')
        ->with('success', 'Venta eliminada correctamente.');
}
}