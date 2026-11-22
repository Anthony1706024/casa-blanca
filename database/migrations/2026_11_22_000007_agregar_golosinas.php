<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Golosinas para la carta (botón "Golosinas"). Productos directos con STOCK_INICIAL unidades cada uno,
// sin receta ni foto; salen en el ticket de bebidas (los entrega la barra).
// Solo agrega los que falten (por nombre). En una base sin productos (instalación nueva o pruebas) no hace nada.
return new class extends Migration
{
    private const STOCK_INICIAL = 60;

    // nombre => precio de venta
    private const GOLOSINAS = [
        'Galleta Soda' => 2,
        'Galleta Picaras' => 2,
        'Galleta Nik' => 2,
        'Galleta Glacitas' => 2,
        'Galleta Casino' => 2,
        'Galleta Gretel' => 2,
        'Galleta Ritz' => 3,
        'Chupetín' => 2,
        'Frugos' => 3,
        'Chocolate Sublime' => 3,
        'Sublime Sonrisas grande' => 4,
        'Chocolate Triángulo D\'Onofrio' => 3,
    ];

    public function up(): void
    {
        if (! DB::table('productos')->exists()) {
            return;
        }

        DB::transaction(function () {
            $categoria = Categoria::firstOrCreate(['nombre' => 'Golosinas'], ['descripcion' => 'Galletas, chocolates y dulces']);
            $subcategoria = Subcategoria::firstOrCreate(
                ['nombre' => 'Golosinas', 'categoria_id' => $categoria->id],
                ['descripcion' => 'Galletas, chocolates y dulces'],
            );

            $ultimo = Producto::where('codigo', 'like', 'GOL%')->pluck('codigo')
                ->map(fn ($codigo) => (int) substr($codigo, 3))
                ->max() ?? 0;

            foreach (self::GOLOSINAS as $nombre => $precio) {
                if (Producto::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->exists()) {
                    continue;
                }

                Producto::create([
                    'codigo' => 'GOL'.str_pad(++$ultimo, 3, '0', STR_PAD_LEFT),
                    'nombre' => $nombre,
                    'tipo' => Producto::DIRECTO,
                    'cocina' => 'bebidas',
                    'destacado' => false,
                    'categoria_id' => $categoria->id,
                    'subcategoria_id' => $subcategoria->id,
                    'stock' => self::STOCK_INICIAL,
                    'precio_compra' => 0,
                    'precio_venta' => $precio,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Quita solo los que nunca se vendieron
        Producto::whereIn('nombre', array_keys(self::GOLOSINAS))
            ->whereDoesntHave('pedidoItems')
            ->get()
            ->each->delete();
    }
};
