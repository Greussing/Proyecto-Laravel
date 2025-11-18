<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimientos_stock';

    protected $fillable = [
        'producto_id',
        'venta_id',
        'cliente',
        'usuario_id',       
        'tipo',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'detalle',
    ];
    
public function usuario()
{
    return $this->belongsTo(User::class, 'usuario_id');
}

    public function clienteRelacion()
    {
        return $this->belongsTo(Cliente::class, 'cliente', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}