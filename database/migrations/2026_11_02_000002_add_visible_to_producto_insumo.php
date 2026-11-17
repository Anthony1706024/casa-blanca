<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Permite ocultar al cliente insumos como sal o aceite, sin dejar de descontarlos del stock
        Schema::table('producto_insumo', function (Blueprint $table) {
            $table->boolean('visible_cliente')->default(true)->after('cantidad');
        });
    }

    public function down()
    {
        Schema::table('producto_insumo', function (Blueprint $table) {
            $table->dropColumn('visible_cliente');
        });
    }
};
