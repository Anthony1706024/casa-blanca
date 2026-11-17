<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    public const UNIDADES = ['unidad', 'presa', 'porción', 'kg', 'g', 'l', 'ml'];

    // Unidades que el cliente entiende como cantidad: singular y plural. Los pesos y volúmenes no se le muestran.
    private const UNIDADES_CONTABLES = [
        'presa' => ['presa', 'presas'],
        'porción' => ['porción', 'porciones'],
    ];

    protected $fillable = ['nombre', 'categoria_insumo_id', 'unidad', 'stock', 'stock_minimo'];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:3',
            'stock_minimo' => 'decimal:3',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaInsumo::class, 'categoria_insumo_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_insumo')->withPivot('cantidad', 'visible_cliente')->withTimestamps();
    }

    public function getStockBajoAttribute()
    {
        return (float) $this->stock <= (float) $this->stock_minimo;
    }

    // Cómo ve el cliente este insumo en una receta: "1 presa de Pato", "2 Huevo" o, si va en gramos, solo "Arroz"
    public function textoParaCliente($cantidad): string
    {
        $numero = self::formatear($cantidad);

        if (isset(self::UNIDADES_CONTABLES[$this->unidad])) {
            [$singular, $plural] = self::UNIDADES_CONTABLES[$this->unidad];

            return $numero.' '.((float) $cantidad === 1.0 ? $singular : $plural).' de '.$this->nombre;
        }

        return $this->unidad === 'unidad' ? $numero.' '.$this->nombre : $this->nombre;
    }

    // Muestra 2.500 como 2.5 y 3.000 como 3
    public static function formatear($cantidad)
    {
        return rtrim(rtrim(number_format((float) $cantidad, 3, '.', ''), '0'), '.');
    }
}
