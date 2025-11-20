<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->index('fecha');
            $table->index('estado');
            // cliente ya es foreignId -> tiene index
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->index('fecha_vencimiento');
            $table->index('nombre');
            // categoria ya es foreignId -> tiene index
        });

        Schema::table('movimientos_stock', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('tipo');
        });

        Schema::table('historials', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('accion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['fecha']);
            $table->dropIndex(['estado']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['fecha_vencimiento']);
            $table->dropIndex(['nombre']);
        });

        Schema::table('movimientos_stock', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['tipo']);
        });

        Schema::table('historials', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['accion']);
        });
    }
};
