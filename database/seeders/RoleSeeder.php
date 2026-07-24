<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['nombre' => 'admin', 'descripcion' => 'Administrador del sistema'],
            ['nombre' => 'mozo', 'descripcion' => 'Mesero / Servicio de restaurante'],
            ['nombre' => 'cajera', 'descripcion' => 'Cajera / Facturación'],
        ];

        foreach ($roles as $rol) {
            Role::create($rol);
        }
    }
}