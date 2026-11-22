<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Un trabajo de la cola de impresión (ver App\Services\ColaImpresionService)
class Impresion extends Model
{
    protected $table = 'impresiones';

    public const PENDIENTE = 'pendiente';

    public const ENVIANDO = 'enviando';

    public const IMPRESA = 'impresa';

    public const ERROR = 'error';

    public const ESTADOS = [
        self::PENDIENTE => 'Pendiente',
        self::ENVIANDO => 'Enviando',
        self::IMPRESA => 'Impresa',
        self::ERROR => 'Error',
    ];

    public const VOUCHER = 'voucher';

    public const COMANDA = 'comanda';

    protected $fillable = ['tipo', 'recibo_id', 'pedido_id', 'descripcion', 'datos', 'estado', 'intentos', 'error', 'tomada_en', 'impresa_en'];

    protected $casts = [
        'intentos' => 'integer',
        'tomada_en' => 'datetime',
        'impresa_en' => 'datetime',
    ];

    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    // Los bytes tal cual se mandan a la ticketera
    public function getBytesAttribute(): string
    {
        return base64_decode($this->datos);
    }
}
