<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cada plato del pedido dice si vino en el pedido original o como adicional de la misma mesa.
// Los adicionales van en su propia línea, así caja sabe qué imprimir para cocina sin repetir lo anterior.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_items', function (Blueprint $table) {
            $table->string('tipo', 20)->default('pedido')->after('cantidad_enviada');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_items', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
