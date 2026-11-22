<?php

namespace App\Models;

use App\Services\ImagenService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    public const DIRECTO = 'directo';

    public const PLATO = 'plato';

    // Dónde se prepara: cada una sale en su propio ticket, en este orden
    public const COCINAS = [
        'cocina_1' => 'Cocina 1',
        'bebidas' => 'Bebidas y tragos',
        'cocina_2' => 'Cocina 2',
    ];

    public const COCINA_POR_DEFECTO = 'cocina_2';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'cocina',
        'destacado',
        'categoria_id',
        'subcategoria_id',
        'stock',
        'precio_compra',
        'precio_venta',
        'descripcion',
        'imagen',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'destacado' => 'boolean',
        ];
    }

    public function historialPrecios()
    {
        return $this->hasMany(ProductoPrecio::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class);
    }

    // Receta: cuánto de cada insumo consume una unidad del plato
    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'producto_insumo')->withPivot('cantidad', 'visible_cliente')->withTimestamps()->orderBy('producto_insumo.id');
    }

    // Lo que ve el cliente de la receta, sin gramos (requiere insumos cargados para no repetir consultas)
    public function getIncluyeAttribute(): array
    {
        return $this->insumos
            ->filter(fn ($insumo) => $insumo->pivot->visible_cliente)
            ->map(fn ($insumo) => $insumo->textoParaCliente($insumo->pivot->cantidad))
            ->values()
            ->all();
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function getCocinaNombreAttribute(): string
    {
        return self::COCINAS[$this->cocina] ?? self::COCINAS[self::COCINA_POR_DEFECTO];
    }

    public function esPlato()
    {
        return $this->tipo === self::PLATO;
    }

    // Unidades que se pueden vender con el stock actual (requiere insumos cargados para no repetir consultas)
    public function getDisponibleAttribute()
    {
        if (! $this->esPlato()) {
            return max(0, (int) $this->stock);
        }

        if ($this->insumos->isEmpty()) {
            return 0;
        }

        // round evita que 0.3 / 0.1 = 2.9999... se cuente como 2
        return (int) $this->insumos
            ->map(fn ($insumo) => floor(round($insumo->stock / $insumo->pivot->cantidad, 6)))
            ->min();
    }

    // Carta: productos con precio y con stock suficiente para al menos una unidad
    public static function disponiblesParaVenta()
    {
        return self::with(['categoria', 'insumos'])
            ->whereNotNull('precio_venta')
            ->orderBy('nombre')
            ->get()
            ->filter(fn ($producto) => $producto->disponible > 0);
    }

    public function getImagenUrlAttribute()
    {
        if ($this->imagen && Storage::disk('public')->exists($this->imagen)) {
            return Storage::url($this->imagen);
        }

        return asset('img/sin-imagen.png');
    }

    // Versión pequeña para las listas; las imágenes antiguas sin miniatura usan la grande
    public function getImagenMiniUrlAttribute()
    {
        if ($this->imagen && Storage::disk('public')->exists(ImagenService::rutaMiniatura($this->imagen))) {
            return Storage::url(ImagenService::rutaMiniatura($this->imagen));
        }

        return $this->imagen_url;
    }

    protected static function booted()
    {
        // Registra todo cambio de precio, venga de donde venga. No toca las ventas ya hechas.
        static::updated(function ($producto) {
            if ($producto->wasChanged('precio_venta')) {
                $producto->historialPrecios()->create([
                    'user_id' => auth()->id(),
                    'precio_anterior' => $producto->getOriginal('precio_venta'),
                    'precio_nuevo' => $producto->precio_venta,
                ]);
            }
        });

        static::deleting(function ($producto) {
            app(ImagenService::class)->eliminar($producto->imagen);
        });
    }
}
