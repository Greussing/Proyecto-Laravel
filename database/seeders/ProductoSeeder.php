<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use Illuminate\Support\Carbon;


class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        Producto::truncate();

        // Categoría 1: Electrónica
        $c1 = [
            ['nombre' => 'Smartphone Galaxy A14', 'cantidad' => 5, 'precio' => 1900000],
            ['nombre' => 'Laptop Dell Inspiron', 'cantidad' => 3, 'precio' => 3900000],
            ['nombre' => 'Smart TV 43” Samsung', 'cantidad' => 3, 'precio' => 3500000],
            ['nombre' => 'Auriculares Bluetooth', 'cantidad' => 6, 'precio' => 450000],
            ['nombre' => 'Disco SSD 1TB', 'cantidad' => 5, 'precio' => 680000],
            ['nombre' => 'Monitor 27” Samsung', 'cantidad' => 3, 'precio' => 1100000],
            ['nombre' => 'Parlante Bluetooth JBL', 'cantidad' => 5, 'precio' => 420000],
            ['nombre' => 'Cámara Web Logitech', 'cantidad' => 6, 'precio' => 220000],
            ['nombre' => 'Mouse Logitech', 'cantidad' => 25, 'precio' => 140000],
            ['nombre' => 'Teclado Mecánico', 'cantidad' => 7, 'precio' => 330000],
        ];

        // Categoría 2: Ropa
        $c2 = [
            ['nombre' => 'Camiseta Deportiva', 'cantidad' => 40, 'precio' => 65000],
            ['nombre' => 'Jean Hombre', 'cantidad' => 20, 'precio' => 135000],
            ['nombre' => 'Buzo Mujer', 'cantidad' => 18, 'precio' => 180000],
            ['nombre' => 'Vestido Casual', 'cantidad' => 8, 'precio' => 230000],
            ['nombre' => 'Shorts Deportivo', 'cantidad' => 22, 'precio' => 65000],
            ['nombre' => 'Remera Básica', 'cantidad' => 35, 'precio' => 55000],
            ['nombre' => 'Chaqueta Mujer', 'cantidad' => 6, 'precio' => 280000],
            ['nombre' => 'Camisa Hombre', 'cantidad' => 10, 'precio' => 160000],
            ['nombre' => 'Conjunto Deportivo', 'cantidad' => 8, 'precio' => 320000],
            ['nombre' => 'Pantalón Hombre', 'cantidad' => 18, 'precio' => 210000],
        ];

        // Categoría 3: Alimentos (con vencimientos)
        $c3 = [
            ['nombre' => 'Arroz 1kg', 'cantidad' => 120, 'precio' => 9500, 'v' => now()->addDays(50)],
            ['nombre' => 'Aceite 1L', 'cantidad' => 60, 'precio' => 17000, 'v' => now()->addDays(120)],
            ['nombre' => 'Azúcar 1kg', 'cantidad' => 100, 'precio' => 12000, 'v' => now()->addDays(180)],
            ['nombre' => 'Leche 1L', 'cantidad' => 120, 'precio' => 7500, 'v' => now()->addDays(5)],
            ['nombre' => 'Pan de Molde', 'cantidad' => 60, 'precio' => 10000, 'v' => now()->addDays(2)],
            ['nombre' => 'Queso Paraguay', 'cantidad' => 0, 'precio' => 30000, 'v' => now()->subDays(10)],
            ['nombre' => 'Harina 1kg', 'cantidad' => 80, 'precio' => 9000, 'v' => now()->addDays(40)],
            ['nombre' => 'Fideos 500g', 'cantidad' => 60, 'precio' => 12000, 'v' => now()->addDays(35)],
            ['nombre' => 'Tomate 1kg', 'cantidad' => 0, 'precio' => 20000, 'v' => now()->subDays(1)],
            ['nombre' => 'Pollo 1kg', 'cantidad' => 15, 'precio' => 42000, 'v' => now()->addDays(7)],
        ];

        // Categoría 4: Accesorios
        $c4 = [
            ['nombre' => 'Reloj Pulsera', 'cantidad' => 0, 'precio' => 220000],
            ['nombre' => 'Lentes de Sol', 'cantidad' => 0, 'precio' => 95000],
            ['nombre' => 'Mochila Escolar', 'cantidad' => 8, 'precio' => 180000],
            ['nombre' => 'Pulsera Hombre', 'cantidad' => 0, 'precio' => 40000],
            ['nombre' => 'Llaveros Decorativos', 'cantidad' => 25, 'precio' => 15000],
            ['nombre' => 'Audífonos Pequeños', 'cantidad' => 10, 'precio' => 130000],
            ['nombre' => 'Gorro de Lana', 'cantidad' => 0, 'precio' => 30000],
            ['nombre' => 'Sombrero de Verano', 'cantidad' => 12, 'precio' => 100000],
            ['nombre' => 'Carpeta Multiuso', 'cantidad' => 20, 'precio' => 85000],
            ['nombre' => 'Cartera Mujer', 'cantidad' => 7, 'precio' => 280000],
        ];

        // Categoría 5: Herramientas
        $c5 = [
            ['nombre' => 'Taladro Eléctrico', 'cantidad' => 6, 'precio' => 430000],
            ['nombre' => 'Martillo', 'cantidad' => 15, 'precio' => 45000],
            ['nombre' => 'Llave Inglesa', 'cantidad' => 10, 'precio' => 60000],
            ['nombre' => 'Sierra Manual', 'cantidad' => 7, 'precio' => 130000],
            ['nombre' => 'Alicates', 'cantidad' => 18, 'precio' => 50000],
            ['nombre' => 'Cinta Métrica 5m', 'cantidad' => 20, 'precio' => 35000],
            ['nombre' => 'Taladro Percutor', 'cantidad' => 4, 'precio' => 680000],
            ['nombre' => 'Juego de Destornilladores', 'cantidad' => 15, 'precio' => 100000],
            ['nombre' => 'Caja de Herramientas', 'cantidad' => 6, 'precio' => 340000],
            ['nombre' => 'Nivel de Burbuja', 'cantidad' => 9, 'precio' => 55000],
        ];

        /// Intercalar (round-robin)
$listas = [$c1, $c2, $c3, $c4, $c5];
$indices = array_fill(0, 5, 0);
$totalFinal = [];

// Lotes secuenciales por categoría
$contadorLotes = [1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1];

while (true) {
    $hayDatos = false;

    for ($i = 0; $i < 5; $i++) {
        if (isset($listas[$i][$indices[$i]])) {
            $p = $listas[$i][$indices[$i]];

            $totalFinal[] = [
                'nombre'            => $p['nombre'],
                'cantidad'          => $p['cantidad'],
                'precio'            => $p['precio'],
                'categoria'         => $i + 1,
                'fecha_vencimiento' => $p['v'] ?? null,
                // 🔹 Solo productos con vencimiento (alimentos) llevan lote
                'lote'              => isset($p['v'])
                    ? 'LOT'.str_pad($contadorLotes[$i+1]++, 4, '0', STR_PAD_LEFT)
                    : null,
            ];

            $indices[$i]++;
            $hayDatos = true;
        }
    }

    if (!$hayDatos) break;
}

foreach ($totalFinal as $p) {
    Producto::create($p);   // ⬅️ Esto dispara el booted() y crea historial
}
    }
}