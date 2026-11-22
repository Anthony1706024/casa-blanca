<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $fillable = ['pedido_id', 'producto_id', 'producto_nombre', 'precio_unitario', 'cantidad', 'cantidad_enviada', 'subtotal'];

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
