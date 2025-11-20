<?php

namespace App\Enums;

enum MetodoPago: string
{
    case EFECTIVO = 'Efectivo';
    case TARJETA = 'Tarjeta';
    case TRANSFERENCIA = 'Transferencia';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
