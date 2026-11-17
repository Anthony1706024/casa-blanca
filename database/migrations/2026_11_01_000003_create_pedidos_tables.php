<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8)->index();
            $table->string('cliente_nombre', 150)->nullable();
            $table->string('mesa', 20)->nullable();
            $table->string('origen', 20);
            $table->foreignId('mozo_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('estado', 20)->default('pendiente')->index();
            $table->decimal('total', 10, 2);
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });

        // Nombre y precio se copian para que el recibo no cambie si luego se edita el producto.
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->string('producto_nombre', 200);
            $table->decimal('precio_unitario', 10, 2);
            $table->unsignedInteger('cantidad');
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_items');
        Schema::dropIfExists('pedidos');
    }
};
