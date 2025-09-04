<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia la tabla antes de insertar (para evitar duplicados)
        Producto::truncate();

        $productos = [
            ['nombre' => 'Smartphone Galaxy A14', 'cantidad' => 5, 'precio' => 1900000, 'categoria' => 1],
            ['nombre' => 'Camiseta Deportiva', 'cantidad' => 40, 'precio' => 65000, 'categoria' => 2],
            ['nombre' => 'Arroz 1kg', 'cantidad' => 120, 'precio' => 9500, 'categoria' => 3],
            ['nombre' => 'Reloj Pulsera', 'cantidad' => 0, 'precio' => 220000, 'categoria' => 4],
            ['nombre' => 'Taladro Eléctrico', 'cantidad' => 6, 'precio' => 430000, 'categoria' => 5],
            ['nombre' => 'Laptop Dell Inspiron', 'cantidad' => 3, 'precio' => 3900000, 'categoria' => 1],
            ['nombre' => 'Jean Hombre', 'cantidad' => 20, 'precio' => 135000, 'categoria' => 2],
            ['nombre' => 'Aceite 1L', 'cantidad' => 60, 'precio' => 17000, 'categoria' => 3],
            ['nombre' => 'Lentes de Sol', 'cantidad' => 0, 'precio' => 95000, 'categoria' => 4],
            ['nombre' => 'Martillo', 'cantidad' => 15, 'precio' => 45000, 'categoria' => 5],
            ['nombre' => 'Smart TV 43” Samsung', 'cantidad' => 3, 'precio' => 3500000, 'categoria' => 1],
            ['nombre' => 'Buzo Mujer', 'cantidad' => 18, 'precio' => 180000, 'categoria' => 2],
            ['nombre' => 'Azúcar 1kg', 'cantidad' => 100, 'precio' => 12000, 'categoria' => 3],
            ['nombre' => 'Mochila Escolar', 'cantidad' => 8, 'precio' => 180000, 'categoria' => 4],
            ['nombre' => 'Llave Inglesa', 'cantidad' => 10, 'precio' => 60000, 'categoria' => 5],
            ['nombre' => 'Auriculares Bluetooth', 'cantidad' => 6, 'precio' => 450000, 'categoria' => 1],
            ['nombre' => 'Shorts Deportivo', 'cantidad' => 22, 'precio' => 65000, 'categoria' => 2],
            ['nombre' => 'Harina 1kg', 'cantidad' => 80, 'precio' => 9000, 'categoria' => 3],
            ['nombre' => 'Pulsera Hombre', 'cantidad' => 0, 'precio' => 40000, 'categoria' => 4],
            ['nombre' => 'Sierra Manual', 'cantidad' => 7, 'precio' => 130000, 'categoria' => 5],
            ['nombre' => 'Disco SSD 1TB', 'cantidad' => 5, 'precio' => 680000, 'categoria' => 1],
            ['nombre' => 'Vestido Casual', 'cantidad' => 8, 'precio' => 230000, 'categoria' => 2],
            ['nombre' => 'Leche 1L', 'cantidad' => 120, 'precio' => 7500, 'categoria' => 3],
            ['nombre' => 'Gorro de Lana', 'cantidad' => 0, 'precio' => 30000, 'categoria' => 4],
            ['nombre' => 'Juego de Destornilladores', 'cantidad' => 15, 'precio' => 100000, 'categoria' => 5],
            ['nombre' => 'Monitor 27” Samsung', 'cantidad' => 3, 'precio' => 1100000, 'categoria' => 1],
            ['nombre' => 'Camisa Hombre', 'cantidad' => 10, 'precio' => 160000, 'categoria' => 2],
            ['nombre' => 'Pan de Molde', 'cantidad' => 60, 'precio' => 10000, 'categoria' => 3],
            ['nombre' => 'Cartera Mujer', 'cantidad' => 7, 'precio' => 280000, 'categoria' => 4],
            ['nombre' => 'Nivel de Burbuja', 'cantidad' => 9, 'precio' => 55000, 'categoria' => 5],
            ['nombre' => 'Cámara Web Logitech', 'cantidad' => 6, 'precio' => 220000, 'categoria' => 1],
            ['nombre' => 'Remera Básica', 'cantidad' => 35, 'precio' => 55000, 'categoria' => 2],
            ['nombre' => 'Queso Paraguay', 'cantidad' => 0, 'precio' => 30000, 'categoria' => 3],
            ['nombre' => 'Llaveros Decorativos', 'cantidad' => 25, 'precio' => 15000, 'categoria' => 4],
            ['nombre' => 'Alicates', 'cantidad' => 18, 'precio' => 50000, 'categoria' => 5],
            ['nombre' => 'Parlante Bluetooth JBL', 'cantidad' => 5, 'precio' => 420000, 'categoria' => 1],
            ['nombre' => 'Chaqueta Mujer', 'cantidad' => 6, 'precio' => 280000, 'categoria' => 2],
            ['nombre' => 'Fideos 500g', 'cantidad' => 60, 'precio' => 12000, 'categoria' => 3],
            ['nombre' => 'Audífonos Pequeños', 'cantidad' => 10, 'precio' => 130000, 'categoria' => 4],
            ['nombre' => 'Cinta Métrica 5m', 'cantidad' => 20, 'precio' => 35000, 'categoria' => 5],
            ['nombre' => 'Mouse Logitech', 'cantidad' => 25, 'precio' => 140000, 'categoria' => 1],
            ['nombre' => 'Conjunto Deportivo', 'cantidad' => 8, 'precio' => 320000, 'categoria' => 2],
            ['nombre' => 'Tomate 1kg', 'cantidad' => 0, 'precio' => 20000, 'categoria' => 3],
            ['nombre' => 'Sombrero de Verano', 'cantidad' => 12, 'precio' => 100000, 'categoria' => 4],
            ['nombre' => 'Taladro Percutor', 'cantidad' => 4, 'precio' => 680000, 'categoria' => 5],
            ['nombre' => 'Teclado Mecánico', 'cantidad' => 7, 'precio' => 330000, 'categoria' => 1],
            ['nombre' => 'Pantalón Hombre', 'cantidad' => 18, 'precio' => 210000, 'categoria' => 2],
            ['nombre' => 'Pollo 1kg', 'cantidad' => 15, 'precio' => 42000, 'categoria' => 3],
            ['nombre' => 'Carpeta Multiuso', 'cantidad' => 20, 'precio' => 85000, 'categoria' => 4],
            ['nombre' => 'Caja de Herramientas', 'cantidad' => 6, 'precio' => 340000, 'categoria' => 5],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}