<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaInsumo extends Model
{
    protected $table = 'categorias_insumo';

    protected $fillable = ['nombre'];

    public function insumos()
    {
        return $this->hasMany(Insumo::class);
    }
}
