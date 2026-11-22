<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    // Vino en el pedido original o se agregó después para la misma mesa
    public const PEDIDO = 'pedido';

    public const ADICIONAL = 'adicional';

    protected $fillable = ['pedido_id', 'producto_id', 'producto_nombre', 'precio_unitario', 'cantidad', 'cantidad_enviada', 'tipo', 'subtotal'];

    public function esAdicional(): bool
    {
        return $this->tipo === self::ADICIONAL;
    }

    // Unidades que todavía no salen en un ticket de cocina
    public function getPorEnviarAttribute(): int
    {
        return max(0, (int) $this->cantidad - (int) $this->cantidad_enviada);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
