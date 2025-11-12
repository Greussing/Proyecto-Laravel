<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'total',
        'metodo_pago',
        'usuario',
        'cliente',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Relación: una venta pertenece a un usuario (vendedor)
    public function usuarioRelacion()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    // Relación: una venta pertenece a un cliente
    public function clienteRelacion()
    {
        return $this->belongsTo(Cliente::class, 'cliente', 'id');
    }

    // Relación: obtener el producto a través del detalle de venta
    public function productoRelacion()
{
    return $this->hasOneThrough(
        Producto::class,        // Modelo final (producto)
        DetalleVenta::class,    // Modelo intermedio (detalle)
        'venta_id',             // FK en detalles
        'id',                   // FK en productos
        'id',                   // Local key en ventas
        'producto_id'           // FK en detalles que apunta a productos
    );
}
    // Relación: detalles de venta
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }
}