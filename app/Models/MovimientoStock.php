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
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Relación: Un movimiento pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeBuscar($query, ?string $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->whereHas('producto', fn($p) => $p->where('nombre', 'like', "%{$search}%"))
              ->orWhereHas('clienteRelacion', fn($c) => $c->where('nombre', 'like', "%{$search}%"))
              ->orWhere('detalle', 'like', "%{$search}%");
        });
    }

    public function scopeFiltrarPorTipo($query, $tipo)
    {
        if (!$tipo) {
            return $query;
        }
        return $query->whereIn('tipo', (array) $tipo);
    }

    public function scopeOrdenar($query, ?string $orden)
    {
        return match ($orden) {
            'fecha_asc'     => $query->orderBy('created_at', 'asc'),
            'cantidad_desc' => $query->orderBy('cantidad', 'desc'),
            'cantidad_asc'  => $query->orderBy('cantidad', 'asc'),
            default         => $query->orderBy('created_at', 'desc'),
        };
    }
}