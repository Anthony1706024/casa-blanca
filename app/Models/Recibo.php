<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $fillable = ['pedido_id', 'cajero_id', 'total'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function cajero()
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function getNumeroAttribute()
    {
        return 'RC-'.str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}
