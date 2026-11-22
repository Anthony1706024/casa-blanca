<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Egreso extends Model
{
    protected $fillable = ['fecha', 'categoria_egreso_id', 'descripcion', 'proveedor', 'metodo', 'comprobante', 'monto', 'user_id'];

    protected $casts = ['monto' => 'decimal:2'];

    // Se guarda solo la fecha (Y-m-d) en MySQL y en SQLite, para poder filtrar y agrupar igual en los dos
    protected function fecha(): Attribute
    {
        return Attribute::make(
            get: fn ($valor) => $valor ? Carbon::parse($valor)->startOfDay() : null,
            set: fn ($valor) => Carbon::parse($valor)->toDateString(),
        );
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaEgreso::class, 'categoria_egreso_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getMetodoNombreAttribute(): ?string
    {
        return $this->metodo ? (Pago::METODOS[$this->metodo] ?? $this->metodo) : null;
    }
}
