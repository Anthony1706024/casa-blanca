<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contenido extends Model
{
    // Campos editables de la página web: clave => [grupo, etiqueta, tipo, valor por defecto]
    public const CAMPOS = [
        'hero_etiqueta' => ['Portada', 'Etiqueta', 'texto', 'Restaurante Campestre · Poroto'],
        'hero_titulo' => ['Portada', 'Título', 'texto', 'Bienvenido a Casa Blanca'],
        'hero_texto' => ['Portada', 'Texto', 'parrafo', 'Disfruta de nuestra piscina, deléitate con nuestra gastronomía y vive momentos inolvidables en familia.'],
        'hero_imagen' => ['Portada', 'Imagen principal', 'imagen', 'img/entrada.webp'],

        'instalaciones_titulo' => ['Instalaciones', 'Título', 'texto', 'Todo para tu disfrute'],
        'instalaciones_texto' => ['Instalaciones', 'Texto', 'parrafo', 'Espacios pensados para pasar un buen día en familia y con amigos.'],

        'platos_titulo' => ['Platos destacados', 'Título', 'texto', 'Delicias de nuestra cocina'],
        'platos_texto' => ['Platos destacados', 'Texto', 'parrafo', 'Algunos de nuestros favoritos. Mira la carta completa y haz tu prepedido.'],

        'galeria_titulo' => ['Galería', 'Título', 'texto', 'Nuestros espacios'],
        'galeria_texto' => ['Galería', 'Texto', 'parrafo', 'Cada rincón de Casa Blanca está pensado para tu bienestar.'],

        'testimonios_titulo' => ['Testimonios', 'Título', 'texto', 'Lo que dicen nuestros clientes'],

        'cta_titulo' => ['Llamado final', 'Título', 'texto', '¿Listo para vivir la experiencia Casa Blanca?'],
        'cta_texto' => ['Llamado final', 'Texto', 'parrafo', 'Te esperamos para disfrutar un día inolvidable junto a los tuyos.'],

        'direccion' => ['Contacto y redes', 'Dirección', 'texto', 'Poroto'],
        'horario' => ['Contacto y redes', 'Horario', 'texto', null],
        'telefono' => ['Contacto y redes', 'Teléfono', 'texto', null],
        'whatsapp' => ['Contacto y redes', 'WhatsApp (con código de país, ej: 51987654321)', 'whatsapp', null],
        'instagram' => ['Contacto y redes', 'Instagram (enlace)', 'url', null],
        'facebook' => ['Contacto y redes', 'Facebook (enlace)', 'url', null],
        'youtube' => ['Contacto y redes', 'YouTube (enlace)', 'url', null],
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

    // Las imágenes por defecto están en public/img; las subidas, en el disco public
    public static function urlImagen(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        return str_starts_with($ruta, 'img/') ? asset($ruta) : asset('storage/'.$ruta);
    }
}
