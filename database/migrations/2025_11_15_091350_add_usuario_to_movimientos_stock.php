<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('movimientos_stock', function (Blueprint $table) {
        $table->foreignId('usuario_id')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete()
            ->after('cliente');
    });
}

public function down()
{
    Schema::table('movimientos_stock', function (Blueprint $table) {
        $table->dropForeign(['usuario_id']);
        $table->dropColumn('usuario_id');
    });
}
};