<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::insert([
            ['nombre' => 'Juan Pérez', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'María González', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Brahian Benítez', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Mateo García', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sofía Rodríguez', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Santiago Fernández', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Valentina López', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Noah Martínez', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Aurora Sánchez', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Miguel Gómez', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}