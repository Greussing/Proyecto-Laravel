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

    /**
     * Relación con el usuario (vendedor)
     */
    public function usuarioRelacion()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    /**
     * Relación con el cliente
     * Columna REAL en BD = 'cliente'
     */
    public function clienteRelacion()
    {
        return $this->belongsTo(Cliente::class, 'cliente', 'id');
    }

    /**
     * Relación: detalles de la venta
     */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Producto relacionado a través del detalle (cuando solo hay uno)
     */
    public function productoRelacion()
    {
        return $this->hasOneThrough(
            Producto::class,        // Modelo final
            DetalleVenta::class,    // Modelo intermedio
            'venta_id',             // FK en detalle_ventas
            'id',                   // PK en productos
            'id',                   // PK en ventas
            'producto_id'           // FK en detalles
        );
    }
}