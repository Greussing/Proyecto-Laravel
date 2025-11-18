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
}