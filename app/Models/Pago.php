<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    public const METODOS = [
        'efectivo' => 'Efectivo',
        'yape' => 'Yape',
        'plin' => 'Plin',
        'tarjeta' => 'Tarjeta',
        'transferencia' => 'Transferencia',
    ];

    protected $fillable = ['recibo_id', 'metodo', 'monto', 'numero_operacion'];

    public function recibo()
    {
        return $this->belongsTo(Recibo::class);
    }

    public function getMetodoNombreAttribute()
    {
        return self::METODOS[$this->metodo] ?? $this->metodo;
    }
}
