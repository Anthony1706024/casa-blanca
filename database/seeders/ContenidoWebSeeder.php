<?php

namespace Database\Seeders;

use App\Models\SitioItem;
use Illuminate\Database\Seeder;

// Contenido inicial de la página de inicio. Las fotos se suben luego desde el panel.
// Los textos de cada sección están en Contenido::CAMPOS.
class ContenidoWebSeeder extends Seeder
{
    public function run()
    {
        $elementos = [
            'instalacion' => [
                ['Tres piscinas', 'Una grande y más profunda, una mediana y una pequeña para los niños.'],
                ['Zona de juegos con trampolín', 'Para que los más pequeños se diviertan todo el día.'],
                ['Áreas verdes y pet friendly', 'Amplios jardines para pasear y descansar. Tu mascota también es bienvenida.'],
                ['Bar', 'Cócteles, cervezas, jarras y cremoladas para refrescarte.'],
                ['Hamacas y columpios', 'Para relajarte a la sombra y desconectarte de la ciudad.'],
                ['Cancha de fulbito y vóley', 'Tenemos net y pelotas para que armes tu partido.'],
                ['Mesas en la naturaleza', 'Come con tranquilidad, lejos del ruido de la ciudad.'],
                ['Zona de parrilla', 'Un espacio para disfrutar de una buena parrilla al aire libre.'],
            ],
            'actividad' => [
                ['Domingos con sonido y animador', 'Música y animación para que toda la familia disfrute la tarde.', 'Todos los domingos'],
                ['Shows en vivo en feriados', 'Payasos y cantantes en vivo: entretenimiento para toda la familia.', 'Feriados y fechas especiales'],
                ['Cortesía por tu cumpleaños', 'Si celebras con tu familia, te obsequiamos un plato, un cóctel o una jarra de cortesía.', 'En tu cumpleaños'],
                ['Juegos y sorteos de platos', 'Participa en nuestras activaciones y gana platos de la carta.', 'En nuestras activaciones'],
                ['Frutas de nuestra tierra', 'Sorteamos y obsequiamos frutas de la zona: piña, palta, lúcuma, carambola y fruta de Poroto.', 'Para nuestros visitantes'],
            ],
            'evento' => [
                ['Promociones de colegio'], ['Bodas'], ['Bautizos'], ['Cumpleaños'], ['Almuerzos corporativos'], ['Reencuentros de colegio'],
            ],
        ];

        foreach ($elementos as $tipo => $lista) {
            foreach ($lista as $orden => $fila) {
                SitioItem::firstOrCreate(
                    ['tipo' => $tipo, 'titulo' => $fila[0]],
                    ['descripcion' => $fila[1] ?? null, 'detalle' => $fila[2] ?? null, 'orden' => $orden, 'activo' => true]
                );
            }
        }
    }
}
