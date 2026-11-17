<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    public const PENDIENTE = 'pendiente';

    public const PAGADO = 'pagado';

    public const ANULADO = 'anulado';

    protected $fillable = ['dni', 'cliente_nombre', 'mesa', 'origen', 'mozo_id', 'estado', 'total', 'observacion'];

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function mozo()
    {
        return $this->belongsTo(User::class, 'mozo_id');
    }

    public function recibo()
    {
        return $this->hasOne(Recibo::class);
    }

    public function getNumeroAttribute()
    {
        return 'PD-'.str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}
