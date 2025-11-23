<?php

namespace App\Http\Controllers;

use App\Enums\MetodoPago;
use App\Enums\VentaEstado;
use App\Http\Requests\DevolucionVentaRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use App\Services\VentaService;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
        $this->authorizeResource(Venta::class, 'venta'); 
    }

    /**
     * INDEX → listado de ventas
     */
    public function index(Request $request)
{
    $query = Venta::with(['usuarioRelacion', 'clienteRelacion', 'detalles'])
        ->withCount(['detalles as cantidad_productos' => function ($q) {
            $q->select(\DB::raw('coalesce(sum(cantidad),0)'));
        }])
        ->filtrarPorFecha($request->input('fecha_desde'), $request->input('fecha_hasta'))
        ->filtrarPorMetodo($request->input('metodo_pago'))
        ->filtrarPorEstado($request->input('estado'))
        ->filtrarPorTotal($request->input('total_min'), $request->input('total_max'))
        ->ordenar($request->input('ordenar'));

    $verTodo   = $request->boolean('verTodo');
    $porPagina = $verTodo ? $query->count() : 15;

    $porPagina = $porPagina > 0 ? $porPagina : 15;

    $ventas = $query->paginate($porPagina)->withQueryString();

    return view('ventas.index', compact('ventas', 'verTodo'));
}
    /**
     * BUSQUEDA → búsqueda AJAX para autocompletar
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

    /**
     * CREATE → formulario de nueva venta
     */
    public function create()
    {
        $clientes  = Cliente::orderBy('nombre')->get();
        $usuarios  = User::orderBy('name')->get();
        $productos = Producto::orderBy('nombre')->get();
        
        // Pasamos enums para que la vista pueda iterarlos si se actualiza
        $metodosPago = MetodoPago::values();
        $estadosVenta = VentaEstado::values();

        return view('ventas.create', compact('clientes', 'usuarios', 'productos', 'metodosPago', 'estadosVenta'));
    }

    /**
     * STORE → guardar venta
     */
    public function store(StoreVentaRequest $request)
    {
        try {
            $this->ventaService->createVenta($request->validated());
            
            return redirect()->route('ventas.index')
                ->with('success', 'Venta registrada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * EDIT → formulario de edición
     */
    public function edit(Venta $venta)
    {
        $clientes  = Cliente::orderBy('nombre')->get();
        $usuarios  = User::orderBy('name')->get();
        $productos = Producto::orderBy('nombre')->get();
        $detalle   = $venta->detalles->first();

        $metodosPago = MetodoPago::values();
        $estadosVenta = VentaEstado::values();

        return view('ventas.edit', compact('venta', 'clientes', 'usuarios', 'productos', 'detalle', 'metodosPago', 'estadosVenta'));
    }

    /**
     * UPDATE → actualizar venta
     */
    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        try {
            $this->ventaService->updateVenta($venta, $request->validated());

            return redirect()->route('ventas.index')
                ->with('success', 'Venta actualizada con éxito.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * DESTROY → eliminar venta
     */
    public function destroy(Venta $venta)
    {
        try {
            $this->ventaService->deleteVenta($venta);

            return redirect()->route('ventas.index')
                ->with('success', 'Venta eliminada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * FORM DEVOLUCIÓN
     */
    public function formDevolucion(Venta $venta)
    {
        $venta->load(['clienteRelacion', 'detalles.producto']);
        return view('ventas.devolucion', compact('venta'));
    }

    /**
     * REGISTRAR DEVOLUCIÓN
     */
    public function registrarDevolucion(DevolucionVentaRequest $request, Venta $venta)
    {
        try {
            $this->ventaService->procesarDevolucion(
                $venta, 
                (int) $request->input('cantidad_devolver'), 
                $request->input('detalle')
            );

            return redirect()->route('movimientos.index')
                ->with('success', 'Devolución registrada correctamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function exportExcel(Request $request)
{
    return Excel::download(
        new \App\Exports\VentaExport($request->all()),
        'ventas.xlsx'
    );
}

public function exportPdf(Request $request)
{
    $query = Venta::with(['usuarioRelacion', 'clienteRelacion', 'detalles'])
        ->filtrarPorFecha($request->input('fecha_desde'), $request->input('fecha_hasta'))
        ->filtrarPorMetodo($request->input('metodo_pago'))
        ->filtrarPorEstado($request->input('estado'))
        ->filtrarPorTotal($request->input('total_min'), $request->input('total_max'))
        ->ordenar($request->input('ordenar'));

    $ventas = $query->get();

    $pdf = Pdf::loadView('ventas.pdf', compact('ventas'))
        ->setPaper('A4', 'portrait');

    return $pdf->download('ventas.pdf');
}
}