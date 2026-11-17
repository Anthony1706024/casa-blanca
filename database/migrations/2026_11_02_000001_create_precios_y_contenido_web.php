<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Registro de cada cambio de precio. Las ventas guardan su propio precio en pedido_items.
        Schema::create('producto_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('precio_anterior', 10, 2)->nullable();
            $table->decimal('precio_nuevo', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('destacado')->default(false)->after('tipo');
        });

        Schema::create('contenidos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 50)->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        Schema::create('sitio_items', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20)->index();
            $table->string('titulo', 150);
            $table->string('descripcion', 500)->nullable();
            $table->string('detalle', 150)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sitio_items');
        Schema::dropIfExists('contenidos');

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('destacado');
        });

        Schema::dropIfExists('producto_precios');
    }
};
