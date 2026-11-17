<?php

namespace App\Models;

use App\Services\ImagenService;
use Illuminate\Database\Eloquent\Model;

class SitioItem extends Model
{
    // Secciones con varios elementos: tipo => textos para el panel
    public const TIPOS = [
        'instalacion' => [
            'nombre' => 'Instalaciones',
            'singular' => 'instalación',
            'icono' => 'fa-swimming-pool',
            'titulo' => 'Nombre',
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
