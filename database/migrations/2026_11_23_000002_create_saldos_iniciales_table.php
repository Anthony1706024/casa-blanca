<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Saldo inicial de caja: con cuánto abre la caja cada día (uno por fecha)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldos_iniciales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->decimal('monto', 10, 2);
            $table->string('nota', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldos_iniciales');
    }
};
