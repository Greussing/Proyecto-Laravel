<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nombre', 'cantidad', 'precio', 'categoria', 'fecha_vencimiento', 'lote'];

    /**
     * Flag temporal para evitar registros duplicados en historial
     * cuando el cambio proviene de ventas/movimientos de stock
     */
    public bool $skipHistoryLog = false;

protected $casts = [
    'precio'            => 'float',
    'cantidad'          => 'integer',
    'fecha_vencimiento' => 'date',
];

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
    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros)
    |--------------------------------------------------------------------------
    */
    public function scopeBuscar($query, ?string $termino)
    {
        return $query->when($termino, fn($q) => $q->where('nombre', 'like', "%{$termino}%"));
    }

    public function scopeFiltrarPorCategorias($query, $categorias)
    {
        if (empty($categorias)) {
            return $query;
        }
        // Maneja tanto array como string único
        return $query->whereIn('categoria', (array) $categorias);
    }

    public function scopeFiltrarPorPrecio($query, $min, $max)
    {
        $precioMin = $this->parsePrecio($min);
        $precioMax = $this->parsePrecio($max);

        return $query
            ->when($precioMin !== null, fn($q) => $q->where('precio', '>=', $precioMin))
            ->when($precioMax !== null, fn($q) => $q->where('precio', '<=', $precioMax));
    }

    public function scopeFiltrarPorStock($query, $stock)
    {
        if (empty($stock)) {
            return $query;
        }

        $stock = (array) $stock;

        return $query->where(function ($q) use ($stock) {
            if (in_array('disponibles', $stock)) {
                $q->orWhere('cantidad', '>', 0);
            }
            if (in_array('agotados', $stock)) {
                $q->orWhere('cantidad', '=', 0);
            }
        });
    }

    public function scopeOrdenar($query, ?string $orden)
    {
        return match ($orden) {
            'nombre_asc'  => $query->orderBy('nombre', 'asc'),
            'nombre_desc' => $query->orderBy('nombre', 'desc'),
            'precio_asc'  => $query->orderBy('precio', 'asc'),
            'precio_desc' => $query->orderBy('precio', 'desc'),
            'stock_asc'   => $query->orderBy('cantidad', 'asc'),
            'stock_desc'  => $query->orderBy('cantidad', 'desc'),
            default       => $query->orderBy('id', 'asc'),
        };
    }

    /**
     * Scope principal que orquesta los filtros (opcional, para mantener compatibilidad si se desea)
     * Pero idealmente usar los scopes individuales en el controlador.
     */
    public function scopeFilter($query, array $filters)
    {
        return $query
            ->buscar($filters['search'] ?? null)
            ->filtrarPorCategorias($filters['categorias'] ?? ($filters['categoria'] ?? null))
            ->filtrarPorPrecio($filters['precio_min'] ?? null, $filters['precio_max'] ?? null)
            ->filtrarPorStock($filters['stock'] ?? null)
            ->ordenar($filters['ordenar'] ?? null);
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

    // Relaciones con otros modelos
    public function historiales()
{
    return $this->hasMany(Historial::class);
}

// Relación con movimientos de stock
public function movimientosStock()
{
    return $this->hasMany(MovimientoStock::class);
}

/*
    |--------------------------------------------------------------------------
    | Caducidad: helpers
    |--------------------------------------------------------------------------
    */

    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->fecha_vencimiento) {
            return null;
        }

        // diffInDays con false para permitir negativos (vencidos)
        return now()->startOfDay()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getEstadoVencimientoAttribute(): string
    {
        if (!$this->fecha_vencimiento) {
            return 'sin_fecha';
        }

        $dias = $this->dias_restantes;

        return match (true) {
            $dias < 0    => 'vencido',
            $dias <= 15  => 'critico',
            $dias <= 30  => 'proximo',
            $dias <= 60  => 'revisar',
            default      => 'ok',
        };
    }

    protected static function booted()
    {
        // El historial se maneja en ProductoController para tener mejor control
        // y evitar duplicados
    }
}