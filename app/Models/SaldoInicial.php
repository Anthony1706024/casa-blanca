<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

// Con cuánto abre la caja un día
class SaldoInicial extends Model
{
    protected $table = 'saldos_iniciales';

    protected $fillable = ['fecha', 'monto', 'nota', 'user_id'];

    protected $casts = ['monto' => 'decimal:2'];

    // Solo la fecha (Y-m-d), igual que Egreso
    protected function fecha(): Attribute
    {
        return Attribute::make(
            get: fn ($valor) => $valor ? Carbon::parse($valor)->startOfDay() : null,
            set: fn ($valor) => Carbon::parse($valor)->toDateString(),
        );
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
