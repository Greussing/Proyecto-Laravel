<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Agrega 'devolucion' al ENUM existente.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE movimientos_stock 
            MODIFY COLUMN tipo 
            ENUM('venta', 'anulacion', 'edicion', 'devolucion')
            NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * Vuelve al ENUM original (sin 'devolucion').
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE movimientos_stock 
            MODIFY COLUMN tipo 
            ENUM('venta', 'anulacion', 'edicion')
            NOT NULL
        ");
    }
};