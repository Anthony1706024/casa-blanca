<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categorias_insumo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->timestamps();
        });

        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->foreignId('categoria_insumo_id')->constrained('categorias_insumo')->onDelete('restrict');
            $table->string('unidad', 20);
            $table->decimal('stock', 12, 3)->default(0);
            $table->decimal('stock_minimo', 12, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('categorias_insumo');
    }
};
