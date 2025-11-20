<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'user_id' => User::factory(),
            'accion' => $this->faker->randomElement(['crear', 'editar', 'eliminar', 'venta']),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
