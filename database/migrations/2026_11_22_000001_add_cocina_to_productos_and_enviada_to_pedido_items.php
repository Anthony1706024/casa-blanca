<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Platos criollos y guarniciones que se preparan en la cocina 1 (el resto va a la cocina 2)
    private const CRIOLLOS_COCINA_1 = [
        'chicharrón de pollo', 'bistec a lo pobre', 'fetuccini a la huancaína', 'lomo saltado',
        'tallarines saltados', 'tallarines verdes con bistec', 'tallarines verdes con pollo',
        'huevo frito', 'papa frita', 'choclo',
    ];

    private const CATEGORIAS_COCINA_1 = ['platos marinos'];

    private const CATEGORIAS_BEBIDAS = ['bebidas', 'cervezas y otros tragos', 'vinos', 'cremoladas'];

    public function up(): void
    {
        // Dónde se prepara cada producto: sale en ese ticket de cocina
        Schema::table('productos', function (Blueprint $table) {
            $table->string('cocina', 20)->default('cocina_2')->after('tipo');
        });

        // Cuánto de cada línea ya se envió a cocina, para imprimir solo los adicionales
        Schema::table('pedido_items', function (Blueprint $table) {
            $table->unsignedInteger('cantidad_enviada')->default(0)->after('cantidad');
        });

        $categorias = DB::table('categorias')->pluck('nombre', 'id')->map(fn ($nombre) => mb_strtolower(trim($nombre)));

        foreach (DB::table('productos')->get(['id', 'nombre', 'categoria_id']) as $producto) {
            $categoria = $categorias[$producto->categoria_id] ?? '';
            $nombre = mb_strtolower(trim($producto->nombre));

            $cocina = match (true) {
                in_array($categoria, self::CATEGORIAS_BEBIDAS, true) => 'bebidas',
                in_array($categoria, self::CATEGORIAS_COCINA_1, true), in_array($nombre, self::CRIOLLOS_COCINA_1, true) => 'cocina_1',
                default => 'cocina_2',
            };

            DB::table('productos')->where('id', $producto->id)->update(['cocina' => $cocina]);
        }
    }

    public function down(): void
    {
        Schema::table('pedido_items', function (Blueprint $table) {
            $table->dropColumn('cantidad_enviada');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('cocina');
        });
    }
};
