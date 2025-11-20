<?php

namespace App\Enums;

enum VentaEstado: string
{
    case PENDIENTE = 'Pendiente';
    case PAGADO = 'Pagado';
    case ANULADO = 'Anulado';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
