<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Historial extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'user_id',
        'accion',
        'descripcion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeSoloProductos($query)
    {
        return $query->whereIn('accion', ['crear', 'editar', 'eliminar']);
    }

    public function scopeBuscar($query, ?string $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->whereHas('producto', function ($q2) use ($search) {
                $q2->where('nombre', 'like', "%{$search}%");
            });
        });
    }

    public function scopeFiltrarPorAccion($query, ?string $accion)
    {
        return $query->when($accion, fn($q) => $q->where('accion', $accion));
    }

    public function scopeOrdenar($query, ?string $orden)
    {
        return match ($orden) {
            'fecha_asc' => $query->orderBy('created_at', 'asc'),
            default     => $query->orderBy('created_at', 'desc'),
        };
    }
}