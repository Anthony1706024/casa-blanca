<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Egresos del restaurante (compras, pagos a distribuidores y al personal, arreglos, servicios...).
// Las categorías las puede ampliar el administrador; aquí se crean las de uso diario.
return new class extends Migration
{
    private const CATEGORIAS = [
        'Compras',
        'Compras de mercado',
        'Compras variadas',
        'Backus (cerveza)',
        'Coca Cola (gaseosas)',
        'Otros distribuidores',
        'Pagos al personal',
        'Electrodomésticos',
        'Arreglos del local',
        'Piscina (cloro, pastillas de cloro)',
        'Obras',
        'Trámites',
        'Agua',
        'Ferretería',
        'Gas',
        'Otros',
    ];

    public function up(): void
    {
        Schema::create('categorias_egreso', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->timestamps();
        });

        Schema::create('egresos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->foreignId('categoria_egreso_id')->constrained('categorias_egreso')->restrictOnDelete();
            $table->string('descripcion', 255);
            $table->string('proveedor', 120)->nullable();
            $table->string('metodo', 20)->nullable();
            $table->string('comprobante', 60)->nullable();
            $table->decimal('monto', 10, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $ahora = now();
        DB::table('categorias_egreso')->insert(array_map(
            fn ($nombre) => ['nombre' => $nombre, 'created_at' => $ahora, 'updated_at' => $ahora],
            self::CATEGORIAS,
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('egresos');
        Schema::dropIfExists('categorias_egreso');
    }
};
