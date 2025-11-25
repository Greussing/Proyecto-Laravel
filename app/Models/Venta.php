<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'fecha' => 'datetime:Y-m-d H:i:s',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    public function usuarioRelacion()
    {
        return $this->belongsTo(User::class, 'usuario', 'id');
    }

    public function clienteRelacion()
    {
        return $this->belongsTo(Cliente::class, 'cliente', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function productoRelacion()
    {
        return $this->hasOneThrough(
            Producto::class,
            DetalleVenta::class,
            'venta_id',
            'id',
            'id',
            'producto_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros)
    |--------------------------------------------------------------------------
    */
    public function scopeFiltrarPorFecha(Builder $query, ?string $desde, ?string $hasta): Builder
    {
        return $query->when($desde, fn ($q) => $q->whereDate('fecha', '>=', $desde))
                     ->when($hasta, fn ($q) => $q->whereDate('fecha', '<=', $hasta));
    }

    public function scopeFiltrarPorMetodo(Builder $query, $metodos): Builder
    {
        if (!$metodos) {
            return $query;
        }
        return $query->whereIn('metodo_pago', (array) $metodos);
    }

    public function scopeFiltrarPorEstado(Builder $query, $estados): Builder
    {
        if (!$estados) {
            return $query;
        }
        return $query->whereIn('estado', (array) $estados);
    }

    public function scopeFiltrarPorTotal(Builder $query, ?string $min, ?string $max): Builder
    {
        if ($min !== null && $min !== '') {
            $minNum = (int) preg_replace('/[^\d]/', '', $min);
            $query->where('total', '>=', $minNum);
        }

        if ($max !== null && $max !== '') {
            $maxNum = (int) preg_replace('/[^\d]/', '', $max);
            $query->where('total', '<=', $maxNum);
        }

        return $query;
    }

    public function scopeOrdenar(Builder $query, ?string $orden): Builder
    {
        return match ($orden) {
            'fecha_asc'  => $query->orderBy('fecha', 'asc'),
            'fecha_desc' => $query->orderBy('fecha', 'desc'),
            'total_asc'  => $query->orderBy('total', 'asc'),
            'total_desc' => $query->orderBy('total', 'desc'),
            default      => $query->orderBy('fecha', 'desc'),
        };
    }
}