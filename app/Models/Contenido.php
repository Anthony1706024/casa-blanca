<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class Contenido extends Model
{
    // Campos editables de la página web: clave => [grupo, etiqueta, tipo, valor por defecto]
    // En los títulos, el texto entre *asteriscos* se muestra en cursiva y color.
    // Una imagen vacía usa la de la portada.
    public const CAMPOS = [
        'hero_etiqueta' => ['Portada', 'Etiqueta', 'texto', 'Restaurante campestre · Poroto · Trujillo'],
        'hero_titulo' => ['Portada', 'Título', 'texto', 'Una casa de campo para *disfrutar* en familia.'],
        'hero_texto' => ['Portada', 'Texto', 'parrafo', 'Piscinas, áreas verdes, juegos y la mejor comida marina y criolla, lejos del ruido de la ciudad. Desde 2015 te atendemos como en casa.'],
        'hero_imagen' => ['Portada', 'Imagen principal', 'imagen', 'img/entrada.webp'],

        'exp_titulo' => ['Tres experiencias', 'Título de la sección', 'texto', 'Elige tu *día* en Casa Blanca.'],
        'exp_texto' => ['Tres experiencias', 'Texto', 'parrafo', 'Un almuerzo en familia, una tarde de piscina o un fin de semana de descanso: aquí cada uno encuentra su lugar.'],
        'exp1_titulo' => ['Tres experiencias', 'Tarjeta 1: título', 'texto', 'Sabor marino y criollo, hecho en casa.'],
        'exp1_texto' => ['Tres experiencias', 'Tarjeta 1: texto', 'parrafo', 'Camarones, ceviches, cecina, cuy y más, servidos con la atención de siempre.'],
        'exp1_imagen' => ['Tres experiencias', 'Tarjeta 1: imagen', 'imagen', null],
        'exp2_titulo' => ['Tres experiencias', 'Tarjeta 2: título', 'texto', 'Tres piscinas y mucho sol.'],
        'exp2_texto' => ['Tres experiencias', 'Tarjeta 2: texto', 'parrafo', 'Una grande y más profunda, una mediana y una pequeña para los niños.'],
        'exp2_imagen' => ['Tres experiencias', 'Tarjeta 2: imagen', 'imagen', null],
        'exp3_titulo' => ['Tres experiencias', 'Tarjeta 3: título', 'texto', 'Descansa, por días o por horas.'],
        'exp3_texto' => ['Tres experiencias', 'Tarjeta 3: texto', 'parrafo', 'Hospedaje para familias que quieren quedarse un poco más en el campo.'],
        'exp3_imagen' => ['Tres experiencias', 'Tarjeta 3: imagen', 'imagen', null],

        'historia_titulo' => ['Nuestra historia', 'Título', 'texto', 'Más que un restaurante, *una casa de campo*.'],
        'historia_texto' => ['Nuestra historia', 'Texto', 'parrafo', "Restaurant Campestre Casa Blanca abrió sus puertas en 2015 en Poroto, a minutos de Trujillo.\nNuestros clientes nos dicen que aquí se sienten como en casa: somos serviciales, atentos con la atención y con la comida, y queremos que te lleves la experiencia de disfrutar de cada rincón del local."],
        'historia_imagen1' => ['Nuestra historia', 'Imagen grande', 'imagen', null],
        'historia_imagen2' => ['Nuestra historia', 'Imagen pequeña', 'imagen', null],
        'cifra1_valor' => ['Nuestra historia', 'Cifra 1', 'texto', '2015'],
        'cifra1_texto' => ['Nuestra historia', 'Cifra 1: texto', 'texto', 'Desde'],
        'cifra2_valor' => ['Nuestra historia', 'Cifra 2', 'texto', '3'],
        'cifra2_texto' => ['Nuestra historia', 'Cifra 2: texto', 'texto', 'Piscinas'],
        'cifra3_valor' => ['Nuestra historia', 'Cifra 3', 'texto', '∞'],
        'cifra3_texto' => ['Nuestra historia', 'Cifra 3: texto', 'texto', 'Áreas verdes'],

        'restaurante_titulo' => ['Restaurante', 'Título', 'texto', 'Comida marina y criolla, *con sazón de casa*.'],
        'restaurante_texto' => ['Restaurante', 'Texto', 'parrafo', 'Nuestra especialidad son los camarones, pero también encontrarás ceviches, pescados, platos criollos, cremoladas y bebidas heladas. Come en el comedor o en las mesas en medio de la naturaleza, con toda la tranquilidad.'],
        'restaurante_lista' => ['Restaurante', 'Lista de puntos (uno por línea)', 'parrafo', "Camarones: nuestra especialidad\nFuentes para compartir en familia\nMesas en la naturaleza, lejos del ruido\nCortesía de platos, cócteles o jarras por cumpleaños\nFrutas de la zona: piña, palta, lúcuma, carambola"],
        'restaurante_imagen' => ['Restaurante', 'Imagen', 'imagen', null],
        'platos_titulo' => ['Restaurante', 'Título de los platos destacados', 'texto', 'Lo más pedido de *nuestra cocina*'],
        'platos_texto' => ['Restaurante', 'Texto de los platos destacados', 'parrafo', null],

        'instalaciones_titulo' => ['Atractivos', 'Título', 'texto', 'Todo para *disfrutar* el día.'],
        'instalaciones_texto' => ['Atractivos', 'Texto', 'parrafo', 'Espacios para nadar, jugar, descansar y desconectarte de la ciudad. Y tu mascota también es bienvenida.'],

        'actividades_titulo' => ['Actividades', 'Título', 'texto', 'Siempre hay *algo que celebrar*.'],
        'actividades_texto' => ['Actividades', 'Texto', 'parrafo', 'Música, shows en vivo, sorteos y sorpresas para que cada visita sea diferente.'],

        'eventos_titulo' => ['Eventos', 'Título', 'texto', 'Tu celebración, *en el campo*.'],
        'eventos_texto' => ['Eventos', 'Texto', 'parrafo', 'Alquilamos el local para tus fechas importantes, con comida, sonido y todo el espacio para tus invitados.'],
        'eventos_imagen' => ['Eventos', 'Imagen grande', 'imagen', null],

        'hospedaje_titulo' => ['Hospedaje', 'Título', 'texto', 'Quédate un poco *más*.'],
        'hospedaje_texto' => ['Hospedaje', 'Texto', 'parrafo', 'Tenemos hospedaje para familias, por días o por horas, para que descanses después de un día de piscina y buena comida.'],
        'hospedaje_lista' => ['Hospedaje', 'Lista de puntos (uno por línea)', 'parrafo', "Habitaciones para familias\nPor días o por horas\nA pasos de las piscinas y áreas verdes"],
        'hospedaje_imagen' => ['Hospedaje', 'Imagen', 'imagen', null],

        'galeria_titulo' => ['Galería', 'Título', 'texto', 'Un recorrido por *Casa Blanca*.'],
        'galeria_texto' => ['Galería', 'Texto', 'parrafo', 'Cada rincón está pensado para que lo disfrutes.'],

        'testimonios_titulo' => ['Testimonios', 'Título', 'texto', 'Lo que dicen *nuestros clientes*.'],

        'cta_titulo' => ['Llamado final', 'Título', 'texto', '¿Listo para tu día en *Casa Blanca*?'],
        'cta_texto' => ['Llamado final', 'Texto', 'parrafo', 'Haz tu pedido, reserva tu mesa o cotiza tu evento. Te esperamos.'],

        'direccion' => ['Contacto y redes', 'Dirección', 'texto', 'Calle Casa Blanca 115, Poroto - Trujillo'],
        'horario' => ['Contacto y redes', 'Horario', 'texto', 'Todos los días, de 9:00 a.m. a 5:00 p.m.'],
        'telefono' => ['Contacto y redes', 'Teléfono', 'texto', '992 078 156'],
        'whatsapp' => ['Contacto y redes', 'WhatsApp (con código de país, ej: 51987654321)', 'whatsapp', '51992078156'],
        'pagos' => ['Contacto y redes', 'Métodos de pago (separados por comas)', 'texto', 'Visa, Mastercard, todas las tarjetas, Yape, Plin, Efectivo'],
        'mapa_enlace' => ['Contacto y redes', 'Enlace de Google Maps', 'url', 'https://maps.app.goo.gl/nggK7vTBBhhjDh3k8'],
        'mapa_coordenadas' => ['Contacto y redes', 'Coordenadas del mapa (latitud,longitud)', 'texto', '-8.0084388,-78.7723257'],
        'instagram' => ['Contacto y redes', 'Instagram (enlace)', 'url', 'https://www.instagram.com/restcasablancaporoto/'],
        'facebook' => ['Contacto y redes', 'Facebook (enlace)', 'url', null],
        'tiktok' => ['Contacto y redes', 'TikTok (enlace)', 'url', null],
        'youtube' => ['Contacto y redes', 'YouTube (enlace)', 'url', null],

        // Datos que salen en el voucher de cobro (ticketera)
        'ticket_nombre' => ['Ticket de cobro', 'Nombre del negocio', 'texto', 'CASA BLANCA'],
        'ticket_subtitulo' => ['Ticket de cobro', 'Subtítulo', 'texto', 'RESTAURANTE CAMPESTRE'],
        'ticket_lugar' => ['Ticket de cobro', 'Lugar', 'texto', 'Poroto – Trujillo – La Libertad'],
        'ticket_ruc' => ['Ticket de cobro', 'RUC', 'texto', '10181327401'],
        'ticket_mensaje' => ['Ticket de cobro', 'Mensaje final', 'texto', 'Esperamos volver a atenderlo pronto.'],
        'ticket_ancho' => ['Ticket de cobro', 'Ancho del papel en mm (80 o 58)', 'texto', '80'],
    ];

    protected $fillable = ['clave', 'valor'];

    // Lo guardado por el administrador, o el valor por defecto si aún no lo cambió
    public static function valores(): array
    {
        $guardados = self::pluck('valor', 'clave');

        $valores = [];
        foreach (self::CAMPOS as $clave => $campo) {
            $valores[$clave] = $guardados->has($clave) ? $guardados[$clave] : $campo[3];
        }

        return $valores;
    }

    // Título con el texto entre *asteriscos* en cursiva. Se escapa antes, así el admin no puede inyectar HTML.
    public static function titulo(?string $texto): HtmlString
    {
        return new HtmlString(preg_replace('/\*(.+?)\*/u', '<em>$1</em>', e($texto ?? '')));
    }

    // Texto de varias líneas como lista, sin líneas vacías
    public static function lineas(?string $texto): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $texto ?? ''))));
    }

    // Las imágenes por defecto están en public/img; las subidas, en el disco public
    public static function urlImagen(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        return str_starts_with($ruta, 'img/') ? asset($ruta) : asset('storage/'.$ruta);
    }
}
