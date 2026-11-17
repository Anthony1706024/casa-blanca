<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Producto => categoría y subcategoría de origen, para poder revertir
    private const PORCIONES = [
        'Arroz blanco' => ['Platos Criollos', 'Guarniciones'],
        'Arroz de trigo' => ['Platos Criollos', 'Guarniciones'],
        'Yuca sancochada' => ['Platos Criollos', 'Guarniciones'],
        'Papa sancochada' => ['Platos Criollos', 'Guarniciones'],
        'Camote' => ['Platos Criollos', 'Guarniciones'],
        'Yuca frita' => ['Platos Criollos', 'Guarniciones'],
        'Papa frita' => ['Platos Criollos', 'Guarniciones'],
        'Choclo' => ['Platos Criollos', 'Guarniciones'],
        'Guacamole' => ['Platos Criollos', 'Guarniciones'],
        'Frejol' => ['Platos Criollos', 'Guarniciones'],
        'Huevo frito' => ['Platos Criollos', 'Guarniciones'],
        'Ensalada criolla' => ['Platos Criollos', 'Guarniciones'],
        'Ensalada fresca' => ['Platos Marinos', 'Entradas'],
    ];

    public function up()
    {
        $porciones = DB::table('categorias')->where('nombre', 'Porciones')->value('id')
            ?? DB::table('categorias')->insertGetId([
                'nombre' => 'Porciones',
                'descripcion' => 'Porciones para agregar a tu plato',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('productos')
            ->whereIn('nombre', array_keys(self::PORCIONES))
            ->update(['categoria_id' => $porciones, 'subcategoria_id' => null]);
    }

    public function down()
    {
        $porciones = DB::table('categorias')->where('nombre', 'Porciones')->value('id');

        foreach (self::PORCIONES as $nombre => [$categoria, $subcategoria]) {
            $categoriaId = DB::table('categorias')->where('nombre', $categoria)->value('id');

            if ($categoriaId) {
                DB::table('productos')->where('nombre', $nombre)->where('categoria_id', $porciones)->update([
                    'categoria_id' => $categoriaId,
                    'subcategoria_id' => DB::table('subcategorias')->where('nombre', $subcategoria)->where('categoria_id', $categoriaId)->value('id'),
                ]);
            }
        }

        // Si el administrador agregó otras porciones, la categoría se conserva
        if (! DB::table('productos')->where('categoria_id', $porciones)->exists()) {
            DB::table('categorias')->where('id', $porciones)->delete();
        }
    }
};
