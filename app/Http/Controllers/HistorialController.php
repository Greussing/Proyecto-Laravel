<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // ✅ Import correcto
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HistorialExport;

class HistorialController extends Controller
{
    // Mostrar historial con búsqueda y paginación
    public function index(Request $request)
    {
        $query = Historial::with('producto')->latest();

        if ($search = $request->get('search')) {
            $query->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('accion', 'like', "%{$search}%");
        }

        $historiales = $query->paginate(15);

        return view('historial.index', compact('historiales'));
    }

    // Descargar PDF
    public function exportPdf()
    {
        $historiales = Historial::with('producto')->latest()->get();
        $pdf = Pdf::loadView('historial.reporte-pdf', compact('historiales')); // ✅ Usa Pdf, no \PDF
        return $pdf->download('HistorialMovimientos.pdf');
    }

    // Descargar Excel
    public function exportExcel()
    {
        return Excel::download(new HistorialExport, 'HistorialMovimientos.xlsx');
    }
}