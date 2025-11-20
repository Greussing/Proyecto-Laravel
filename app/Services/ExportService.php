<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    /**
     * Genera y descarga un PDF a partir de una vista.
     */
    public function downloadPdf(string $view, array $data, string $filename, string $paper = 'a4', string $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper($paper, $orientation);

        // Asegurar extensión
        if (!str_ends_with($filename, '.pdf')) {
            $filename .= '.pdf';
        }

        return $pdf->download($filename);
    }

    /**
     * Genera y descarga un Excel usando una clase Export.
     */
    public function downloadExcel(object $exportClass, string $filename)
    {
        // Asegurar extensión
        if (!str_ends_with($filename, '.xlsx')) {
            $filename .= '.xlsx';
        }

        return Excel::download($exportClass, $filename);
    }
}
