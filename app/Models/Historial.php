<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'accion',
        'descripcion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}