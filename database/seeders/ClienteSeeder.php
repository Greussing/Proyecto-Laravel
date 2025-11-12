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
        ]);
    }
}