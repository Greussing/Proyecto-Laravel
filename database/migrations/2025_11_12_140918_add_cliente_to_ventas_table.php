<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('cliente')     
                ->nullable()
                ->constrained('clientes')     
                ->onDelete('set null')
                ->after('usuario_id');        // opcional, solo para orden
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['cliente']);
            $table->dropColumn('cliente');
        });
    }
};
