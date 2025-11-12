<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 🔒 Desactivar claves foráneas temporalmente
        Schema::disableForeignKeyConstraints();

        // 👇 Ejecutar los seeders normalmente
        $this->call([
            CategoriaSeeder::class,
            UserSeeder::class,
            ProductoSeeder::class,
            ClienteSeeder::class,
        ]);

        // 🔓 Volver a activar las claves foráneas
        Schema::enableForeignKeyConstraints();
    }
}