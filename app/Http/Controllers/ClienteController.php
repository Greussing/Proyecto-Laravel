<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Métricas Generales
        $totalClientes = Cliente::count();
        $nuevosClientes = Cliente::whereMonth('created_at', now()->month)->count();
        
        $mejorCliente = Cliente::withSum('ventas', 'total')
            ->orderByDesc('ventas_sum_total')
            ->first();

        // Query Principal
        $query = Cliente::withSum('ventas', 'total')
            ->withMax('ventas', 'fecha'); // Última compra

        // Búsqueda
        if ($request->filled('search')) {
            $termino = $request->search;
            $query->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%")
                  ->orWhere('telefono', 'like', "%{$termino}%");
            });
        }

        // Ordenamiento
        if ($request->filled('ordenar')) {
            switch ($request->ordenar) {
                case 'mayor_gasto':
                    $query->orderByDesc('ventas_sum_total');
                    break;
                case 'menor_gasto':
                    $query->orderBy('ventas_sum_total');
                    break;
                case 'recientes':
                    $query->latest();
                    break;
                case 'antiguos':
                    $query->oldest();
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $clientes = $query->paginate(10)->withQueryString();
        
        return view('clientes.index', compact('clientes', 'totalClientes', 'nuevosClientes', 'mejorCliente'));
    }

    /**
     * Búsqueda AJAX
     */
    public function busqueda(Request $request)
    {
        $termino = $request->input('search');

        $clientes = Cliente::withSum('ventas', 'total')
            ->withMax('ventas', 'fecha')
            ->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
            })
            ->take(20)
            ->get();

        return response()->json($clientes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clientes,email',
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Ya existe un cliente con ese email.',
            'telefono.max' => 'El teléfono no puede tener más de 50 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 500 caracteres.',
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id,
            'telefono' => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre del cliente es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe ser una dirección válida.',
            'email.unique' => 'Ya existe un cliente con ese email.',
            'telefono.max' => 'El teléfono no puede tener más de 50 caracteres.',
            'direccion.max' => 'La dirección no puede tener más de 500 caracteres.',
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        // Verificar si tiene ventas asociadas
        if ($cliente->ventas()->count() > 0) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene ventas asociadas.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new \App\Exports\ClientesExport($request->all()), 'clientes.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Cliente::withSum('ventas', 'total')
            ->withMax('ventas', 'fecha');

        if ($request->filled('search')) {
            $termino = $request->search;
            $query->where(function($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
            });
        }

        if ($request->filled('ordenar')) {
            switch ($request->ordenar) {
                case 'mayor_gasto': $query->orderByDesc('ventas_sum_total'); break;
                case 'menor_gasto': $query->orderBy('ventas_sum_total'); break;
                case 'recientes': $query->latest(); break;
                case 'antiguos': $query->oldest(); break;
                default: $query->latest();
            }
        } else {
            $query->latest();
        }

        $clientes = $query->get();
        $pdf = Pdf::loadView('clientes.pdf', compact('clientes'));
        return $pdf->download('clientes.pdf');
    }
}
