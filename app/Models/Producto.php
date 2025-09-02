<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'cantidad', 'precio', 'categoria'];

    public function categoriaRelacion()
    {
        return $this->belongsTo(Categoria::class, 'categoria', 'id');
    }
}
