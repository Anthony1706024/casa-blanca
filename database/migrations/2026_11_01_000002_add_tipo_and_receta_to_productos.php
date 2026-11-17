<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Los productos existentes quedan como 'directo' y conservan su stock actual.
        Schema::table('productos', function (Blueprint $table) {
            $table->string('tipo', 20)->default('directo')->after('nombre');
        });

        Schema::create('producto_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('restrict');
            $table->decimal('cantidad', 12, 3);
            $table->timestamps();
            $table->unique(['producto_id', 'insumo_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('producto_insumo');

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
