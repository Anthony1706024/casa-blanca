<?php

namespace App\Models;

use App\Services\ImagenService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SitioItem extends Model
{
    // Secciones con varios elementos: tipo => textos para el panel
    public const TIPOS = [
        'instalacion' => [
            'nombre' => 'Atractivos',
            'singular' => 'atractivo',
            'icono' => 'fa-swimming-pool',
            'titulo' => 'Nombre (el ícono se elige por el nombre: piscina, fulbito, vóley, mascotas, bar...)',
            'descripcion' => 'Descripción',
            'detalle' => null,
        ],
        'actividad' => [
            'nombre' => 'Actividades',
            'singular' => 'actividad',
            'icono' => 'fa-music',
            'titulo' => 'Nombre',
            'descripcion' => 'Descripción',
            'detalle' => 'Cuándo (ej: Todos los domingos)',
        ],
        'evento' => [
            'nombre' => 'Eventos',
            'singular' => 'tipo de evento',
            'icono' => 'fa-champagne-glasses',
            'titulo' => 'Tipo de evento',
            'descripcion' => 'Descripción',
            'detalle' => null,
        ],
        'galeria' => [
            'nombre' => 'Galería',
            'singular' => 'foto',
            'icono' => 'fa-images',
            'titulo' => 'Título de la foto',
            'descripcion' => null,
            'detalle' => null,
        ],
        'testimonio' => [
            'nombre' => 'Testimonios',
            'singular' => 'testimonio',
            'icono' => 'fa-quote-left',
            'titulo' => 'Nombre del cliente',
            'descripcion' => 'Testimonio',
            'detalle' => 'Detalle (ej: Cliente desde 2024)',
        ],
    ];

    protected $table = 'sitio_items';

    protected $fillable = ['tipo', 'titulo', 'descripcion', 'detalle', 'imagen', 'orden', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function getImagenUrlAttribute()
    {
        return Contenido::urlImagen($this->imagen);
    }

    // Ícono de Font Awesome elegido por palabras del título; el admin no tiene que saber nombres de íconos
    // El orden importa: gana la primera palabra que aparezca en el título
    private const ICONOS = [
        'sorteo' => 'fa-gift', 'reencuentro' => 'fa-people-group',
        'piscina' => 'fa-person-swimming', 'alberca' => 'fa-person-swimming',
        'hamaca' => 'fa-umbrella-beach', 'columpio' => 'fa-child-reaching',
        'trampolin' => 'fa-child-reaching', 'juego' => 'fa-child-reaching', 'infantil' => 'fa-child-reaching',
        'voley' => 'fa-volleyball', 'fulbito' => 'fa-futbol', 'futbol' => 'fa-futbol', 'cancha' => 'fa-futbol',
        'mascota' => 'fa-paw', 'pet' => 'fa-paw',
        'parrilla' => 'fa-fire', 'bar' => 'fa-martini-glass-citrus', 'coctel' => 'fa-martini-glass-citrus',
        'mesa' => 'fa-tree', 'naturaleza' => 'fa-tree', 'verde' => 'fa-leaf', 'jardin' => 'fa-leaf',
        'sonido' => 'fa-music', 'musica' => 'fa-music', 'animador' => 'fa-microphone', 'cantante' => 'fa-microphone',
        'show' => 'fa-masks-theater', 'payaso' => 'fa-masks-theater', 'feriado' => 'fa-masks-theater',
        'hospedaje' => 'fa-bed', 'habitacion' => 'fa-bed',
        'cumpleanos' => 'fa-cake-candles', 'sorteo' => 'fa-gift', 'activacion' => 'fa-gift', 'cortesia' => 'fa-gift',
        'fruta' => 'fa-lemon', 'boda' => 'fa-ring', 'bautizo' => 'fa-dove', 'promocion' => 'fa-graduation-cap',
        'colegio' => 'fa-graduation-cap', 'reencuentro' => 'fa-people-group', 'corporativ' => 'fa-briefcase',
        'empresa' => 'fa-briefcase', 'restaurante' => 'fa-utensils',
    ];

    public function getIconoAttribute(): string
    {
        $texto = strtolower(Str::ascii($this->titulo));
        foreach (self::ICONOS as $palabra => $icono) {
            if (str_contains($texto, $palabra)) {
                return $icono;
            }
        }

        return 'fa-leaf';
    }

    public static function publicados(string $tipo)
    {
        return self::where('tipo', $tipo)->where('activo', true)->orderBy('orden')->orderBy('id')->get();
    }

    protected static function booted()
    {
        static::deleting(function ($item) {
            app(ImagenService::class)->eliminar($item->imagen);
        });
    }
}
