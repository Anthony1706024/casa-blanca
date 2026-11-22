<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cola de impresión: la app deja los bytes ESC/POS y el agente de la PC de caja los imprime
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impresiones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);                  // voucher | comanda
            $table->foreignId('recibo_id')->nullable()->constrained('recibos')->nullOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->string('descripcion');
            $table->longText('datos');                   // bytes ESC/POS en base64
            $table->string('estado', 20)->default('pendiente')->index();
            $table->unsignedInteger('intentos')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('tomada_en')->nullable();
            $table->timestamp('impresa_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impresiones');
    }
};
