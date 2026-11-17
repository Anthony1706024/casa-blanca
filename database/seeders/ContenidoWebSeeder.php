<?php

namespace Database\Seeders;

use App\Models\SitioItem;
use Illuminate\Database\Seeder;

class ContenidoWebSeeder extends Seeder
{
    public function run()
    {
        $instalaciones = [
            ['Piscina', 'Refréscate y disfruta de un día de sol con toda la familia.'],
            ['Restaurante', 'Platos marinos y criollos preparados con ingredientes frescos.'],
            ['Jardines', 'Áreas verdes para descansar, pasear y compartir al aire libre.'],
            ['Bar y Terraza', 'Cócteles, cervezas y bebidas para disfrutar al aire libre.'],
            ['Salón de Eventos', 'Un espacio para tus celebraciones familiares y reuniones.'],
            ['Zona Infantil', 'Un lugar para que los más pequeños se diviertan.'],
        ];

        foreach ($instalaciones as $orden => [$titulo, $descripcion]) {
            SitioItem::firstOrCreate(
                ['tipo' => 'instalacion', 'titulo' => $titulo],
                ['descripcion' => $descripcion, 'orden' => $orden, 'activo' => true]
            );
        }
    }
}
