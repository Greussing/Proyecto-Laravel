<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_stock', function (Blueprint $table) {
            $table->foreignId('cliente')
                ->nullable()
                ->constrained('clientes')   // FK a tabla clientes
                ->nullOnDelete()           // si se borra el cliente, queda null
                ->after('producto_id');    // opcional, solo para orden
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_stock', function (Blueprint $table) {
            $table->dropForeign(['cliente']);
            $table->dropColumn('cliente');
        });
    }
};