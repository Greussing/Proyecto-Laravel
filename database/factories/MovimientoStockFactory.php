<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoStockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'venta_id' => Venta::factory(),
            'cliente' => Cliente::factory(),
            'usuario_id' => User::factory(),
            'tipo' => $this->faker->randomElement(['venta', 'devolucion', 'ajuste']),
            'cantidad' => $this->faker->numberBetween(1, 50),
            'stock_antes' => $this->faker->numberBetween(50, 100),
            'stock_despues' => $this->faker->numberBetween(0, 150),
            'detalle' => $this->faker->sentence(),
        ];
    }
}
