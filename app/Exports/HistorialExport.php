<?php

namespace App\Exports;

use App\Models\Historial;
use Maatwebsite\Excel\Concerns\FromCollection;

class HistorialExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Historial::all();
    }
}
