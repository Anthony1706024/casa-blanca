<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->unique()->constrained('pedidos')->onDelete('restrict');
            $table->foreignId('cajero_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_id')->constrained('recibos')->onDelete('cascade');
            $table->string('metodo', 20);
            $table->decimal('monto', 10, 2);
            $table->string('numero_operacion', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('recibos');
    }
};
