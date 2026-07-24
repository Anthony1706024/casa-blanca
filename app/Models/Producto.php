<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'categoria_id',
        'subcategoria_id',
        'stock',
        'precio_compra',
        'precio_venta',
        'descripcion',
        'imagen'
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class);
    }

    public function getImagenUrlAttribute()
    {
        if ($this->imagen && Storage::disk('public')->exists($this->imagen)) {
            return Storage::url($this->imagen);
        }
        return asset('img/sin-imagen.png');
    }

    protected static function booted()
    {
        static::deleting(function ($producto) {
            if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
        });
    }
}