<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'cantidad', 'precio', 'categoria'];

    protected $casts = [
        'precio'   => 'float',
        'cantidad' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Eventos del modelo → Reordenar IDs al borrar
    |--------------------------------------------------------------------------
    */
    protected static function booted()
    {
        static::deleted(function () {
            self::reordenarIds();
        });
    }

    public static function reordenarIds()
    {
        // Reordenar los IDs en orden consecutivo
        DB::statement('SET @count = 0');
        DB::statement('UPDATE productos SET id = (@count := @count + 1) ORDER BY id');

        // Resetear el AUTO_INCREMENT para que el próximo siga correctamente
        DB::statement('ALTER TABLE productos AUTO_INCREMENT = 1');
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    public function categoriaRelacion()
    {
        return $this->belongsTo(Categoria::class, 'categoria', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope → filtros dinámicos
    |--------------------------------------------------------------------------
    */
    public function scopeFilter($query, $filters)
    {
        // Normalizar valores de precio mínimo y máximo
        $precioMin = isset($filters['precio_min'])
            ? $this->parsePrecio($filters['precio_min'])
            : null;

        $precioMax = isset($filters['precio_max'])
            ? $this->parsePrecio($filters['precio_max'])
            : null;

        return $query
            ->when($filters['search'] ?? null, fn($q, $search) =>
                $q->where('nombre', 'like', '%'.$search.'%')
            )
            ->when(!empty($filters['categorias']) || !empty($filters['categoria']), function ($q) use ($filters) {
                $cats = $filters['categorias'] ?? [$filters['categoria']];
                $q->whereIn('categoria', (array) $cats);
            })
            ->when($precioMin !== null, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax !== null, fn($q) => $q->where('precio', '<=', $precioMax))
            ->when($filters['stock'] ?? null, function ($q, $stock) {
                $stock = (array) $stock;
                if (in_array('disponibles', $stock) && !in_array('agotados', $stock)) {
                    $q->where('cantidad', '>', 0);
                }
                if (in_array('agotados', $stock) && !in_array('disponibles', $stock)) {
                    $q->where('cantidad', '=', 0);
                }
            })
            ->when($filters['ordenar'] ?? null, function ($q, $orden) {
                return match ($orden) {
                    'nombre_asc'  => $q->orderBy('nombre', 'asc'),
                    'nombre_desc' => $q->orderBy('nombre', 'desc'),
                    'precio_asc'  => $q->orderBy('precio', 'asc'),
                    'precio_desc' => $q->orderBy('precio', 'desc'),
                    'stock_asc'   => $q->orderBy('cantidad', 'asc'),
                    'stock_desc'  => $q->orderBy('cantidad', 'desc'),
                    default       => $q->orderBy('id', 'asc'),
                };
            }, fn($q) => $q->orderBy('id', 'asc'));
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators & Accessors
    |--------------------------------------------------------------------------
    */
    public function setPrecioAttribute($value)
    {
        $this->attributes['precio'] = $this->parsePrecio($value);
    }

    public function getValorAttribute()
    {
        return number_format($this->precio, 0, ',', '.'); // Ej: 1.234.567
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    protected function parsePrecio($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $valor = (string) $value;

        // Quitar caracteres que no sean dígitos, puntos o comas
        $valor = preg_replace('/[^\d\.,]/', '', $valor);

        if (str_contains($valor, ',')) {
            // Formato europeo: "1.234.567,89"
            $valor = str_replace('.', '', $valor);   // quitar separadores de miles
            $valor = str_replace(',', '.', $valor);  // usar punto como decimal
        } else {
            // Formato americano: "1234.56"
            if (preg_match('/\.\d{1,2}$/', $valor)) {
                // Punto como decimal → separar entero y decimal
                $pos = strrpos($valor, '.');
                $ent = substr($valor, 0, $pos);
                $dec = substr($valor, $pos);
                $ent = str_replace('.', '', $ent); // quitar puntos de miles
                $valor = $ent.$dec;
            } else {
                // Solo puntos como miles → quitarlos
                $valor = str_replace('.', '', $valor);
            }
        }

        return (float) $valor;
    }
}