<?php

namespace Database\Factories;

use App\Enums\MetodoPago;
use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fecha' => $this->faker->dateTimeThisYear(),
            'total' => $this->faker->randomFloat(2, 50, 5000),
            'metodo_pago' => $this->faker->randomElement(MetodoPago::values()),
            'estado' => $this->faker->randomElement(VentaEstado::values()),
            'usuario' => User::factory(),
            'cliente' => Cliente::factory(),
        ];
    }
}
