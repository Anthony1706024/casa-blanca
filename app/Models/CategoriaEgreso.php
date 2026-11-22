<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaEgreso extends Model
{
    protected $table = 'categorias_egreso';

    protected $fillable = ['nombre'];

    public function egresos()
    {
        return $this->hasMany(Egreso::class);
    }
}
