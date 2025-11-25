<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $request;

    public function __construct(array $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = User::query();

        if (isset($this->request['search']) && $this->request['search']) {
            $search = $this->request['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($this->request['ordenar']) && $this->request['ordenar']) {
            switch ($this->request['ordenar']) {
                case 'recientes': $query->latest(); break;
                case 'antiguos': $query->oldest(); break;
                case 'nombre_asc': $query->orderBy('name'); break;
                case 'nombre_desc': $query->orderByDesc('name'); break;
                default: $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Email',
            'Fecha Registro',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
