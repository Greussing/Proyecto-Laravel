<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_stock', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->onDelete('cascade');

            $table->foreignId('venta_id')
                ->nullable()
                ->constrained('ventas')
                ->onDelete('set null');

            $table->enum('tipo', ['venta', 'eliminacion', 'edicion']);

            // Cantidad movida (puede ser positiva o negativa)
            $table->integer('cantidad');

            // Stock antes y después del movimiento
            $table->integer('stock_antes');
            $table->integer('stock_despues');

            // Texto libre opcional
            $table->text('detalle')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
    }
};