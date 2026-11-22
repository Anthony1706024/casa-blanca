<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Subcategoria;
use Database\Seeders\RecetasSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Vuelven a la carta las jarras de jugo, los frozen y los cócteles (se quitaron al actualizar la carta el 18/11).
// Solo agrega los que falten (por nombre): si el admin ya creó alguno, se respeta. Cada uno lleva su foto
// anterior (ya está en storage/app/public/productos).
// Se venden como productos directos con STOCK_INICIAL unidades cada uno (no descuentan insumos). Igual se les
// guarda la receta estimada de RecetasSeeder, solo para que la carta muestre "Qué trae".
// En una base sin productos (instalación nueva o pruebas) no hace nada.
return new class extends Migration
{
    private const STOCK_INICIAL = 60;

    // nombre => [precio de venta, costo estimado, foto, destacado]
    private const JUGOS = [
        'Jarra de piña Golden' => [20, 13, '9e369325-037c-44db-9d8e-e21db878ec44', false],
        'Jarra de chicha morada' => [20, 13, '41a51e65-d2d5-42ca-8ac9-7fa749b0f765', true],
        'Jarra de maracuyá' => [20, 13, 'eac3bf26-1b42-4605-806f-e4c66bf6ba02', false],
        'Jarra de guanábana' => [20, 13, '2a0eb4ac-ad7e-4f45-98a2-9cebb2d68652', false],
        'Jarra de carambola' => [20, 13, '55ac9f55-6598-48d1-8896-dd84b829c5cf', false],
        'Jarra de guayaba' => [20, 13, '93e2c6d5-31f2-4d06-9258-a9a36c9823a5', false],
        'Limonada Frozen' => [25, 16, '9bb99de7-c8df-4849-82cc-cd245e05d03e', false],
        'Piña Golden Frozen' => [25, 16, 'a739f32a-d713-4079-bf6e-d7f25a554f31', false],
        'Maracuyá Frozen' => [25, 16, '2b0b31d2-4393-4b36-a0b6-f19ae2b06e9f', false],
    ];

    private const COCTELES = [
        'Pisco Sour' => [28, 18, 'f372215a-a12d-4ad1-94fc-4aadfd6a0b73', true],
        'Piña Colada' => [20, 13, '3a21ffe5-3c5a-4e42-8a89-0d5de2282164', false],
        'Cuba Libre' => [20, 13, '9aa3ea48-102e-468e-a314-0a98cad5c6df', false],
        'Daiquiri' => [28, 18, 'cc3e2176-c4fb-4435-bd0c-c270aef8469e', false],
        'Mojito de maracuyá' => [20, 13, '003ea2f6-220a-4bd0-a935-a8bd6734b1a5', false],
        'Mojito de limón' => [20, 13, 'c9b1429b-e58a-4693-8693-17b18fc9b29c', false],
        'Chilcano clásico' => [20, 13, '494663ae-839b-493d-a756-01fcc07b240d', false],
        'Peruanísimo' => [28, 18, '24f10de0-a03b-40e5-aa70-1a2325e08139', false],
        'Machu Picchu' => [28, 18, '50f19cf3-e9a9-4c92-b4c4-5490b9894fe6', false],
        'Canario' => [20, 13, 'df4275be-2762-4967-be52-d768155a5a14', false],
        'Destornillador' => [20, 13, '480643de-8ed9-41e7-b4e2-c3495167f1f7', false],
        'Chilcano de piña' => [20, 13, '7246c574-74c7-4277-af27-005827545b8a', false],
        'Chilcano de maracuyá' => [20, 13, '2a348c9d-8b94-44e2-8897-aea13c4c3e66', false],
        'Sex on the Beach' => [28, 18, 'e7fe333b-2f6c-458f-9de7-50b93b0b2245', false],
        'Laguna Azul' => [28, 18, 'a6fa69f9-44ec-4719-ab1a-017362990bc9', false],
        'Jarra de sangría' => [70, 45, '7c7f02f1-150c-4652-9900-aa5529ceb96a', false],
    ];

    public function up(): void
    {
        if (! DB::table('productos')->exists()) {
            return;
        }

        $nuevos = DB::transaction(function () {
            $bebidas = Categoria::firstOrCreate(['nombre' => 'Bebidas'], ['descripcion' => 'Bebidas frías y calientes']);
            $jugos = Subcategoria::firstOrCreate(
                ['nombre' => 'Jugos Naturales', 'categoria_id' => $bebidas->id],
                ['descripcion' => 'Jarras de jugo de frutas naturales y frozen'],
            );

            $cocteles = Categoria::firstOrCreate(['nombre' => 'Cócteles'], ['descripcion' => 'Bebidas preparadas con licores y frutas']);
            $subcocteles = Subcategoria::firstOrCreate(
                ['nombre' => 'Cócteles', 'categoria_id' => $cocteles->id],
                ['descripcion' => 'Cócteles clásicos y de la casa'],
            );

            return array_merge(
                $this->agregar(self::JUGOS, 'BEB', $bebidas->id, $jugos->id),
                $this->agregar(self::COCTELES, 'COC', $cocteles->id, $subcocteles->id),
            );
        });

        if (! $nuevos) {
            return;
        }

        // Receta para "Qué trae" (crea solo los insumos que falten; no toca stock de insumos ni otras recetas).
        // El seeder los deja como plato: luego pasan a directos con su propio stock.
        $recetas = new RecetasSeeder;
        $recetas->solo = $nuevos;
        $recetas->run();

        Producto::whereIn('nombre', $nuevos)->update(['tipo' => Producto::DIRECTO, 'stock' => self::STOCK_INICIAL]);
    }

    public function down(): void
    {
        // Quita solo los que nunca se vendieron (los vendidos siguen en pedidos y recibos)
        Producto::whereIn('nombre', array_keys(self::JUGOS + self::COCTELES))
            ->whereDoesntHave('pedidoItems')
            ->get()
            ->each->delete();
    }

    // Crea los que no existan, con el siguiente código libre del prefijo (BEB014, COC001...). Devuelve sus nombres.
    private function agregar(array $productos, string $prefijo, int $categoriaId, int $subcategoriaId): array
    {
        $ultimo = Producto::where('codigo', 'like', $prefijo.'%')->pluck('codigo')
            ->map(fn ($codigo) => (int) substr($codigo, strlen($prefijo)))
            ->max() ?? 0;

        $creados = [];
        foreach ($productos as $nombre => [$precio, $costo, $foto, $destacado]) {
            if (Producto::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->exists()) {
                continue;
            }

            Producto::create([
                'codigo' => $prefijo.str_pad(++$ultimo, 3, '0', STR_PAD_LEFT),
                'nombre' => $nombre,
                'tipo' => Producto::DIRECTO,
                'cocina' => 'bebidas',
                'destacado' => $destacado,
                'categoria_id' => $categoriaId,
                'subcategoria_id' => $subcategoriaId,
                'stock' => 0,
                'precio_compra' => $costo,
                'precio_venta' => $precio,
                'imagen' => "productos/{$foto}.webp",
            ]);
            $creados[] = $nombre;
        }

        return $creados;
    }
};
