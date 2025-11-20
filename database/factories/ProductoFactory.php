<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(3, true),
            'cantidad' => $this->faker->numberBetween(0, 100),
            'precio' => $this->faker->randomFloat(2, 10, 1000),
            'categoria' => Categoria::factory(),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('-1 month', '+3 months'),
        ];
    }
}
