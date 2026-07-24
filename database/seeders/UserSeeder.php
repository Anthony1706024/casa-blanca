<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Obtener roles
        $adminRole = Role::where('nombre', 'admin')->first();
        $mozoRole = Role::where('nombre', 'mozo')->first();
        $cajeraRole = Role::where('nombre', 'cajera')->first();

        // Usuario Admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@casablanca.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        // Usuario Mozo
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@casablanca.com',
            'password' => Hash::make('12345678'),
            'role_id' => $mozoRole->id,
            'email_verified_at' => now(),
        ]);

        // Usuario Cajera
        User::create([
            'name' => 'María Flores',
            'email' => 'maria@casablanca.com',
            'password' => Hash::make('12345678'),
            'role_id' => $cajeraRole->id,
            'email_verified_at' => now(),
        ]);
    }
}