<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // ← Agregar role_id aquí
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación con roles
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Verificar si tiene un rol específico
    public function hasRole($roleName)
    {
        return $this->role && $this->role->nombre === $roleName;
    }

    // Helpers para cada rol
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isMozo()
    {
        return $this->hasRole('mozo');
    }

    public function isCajera()
    {
        return $this->hasRole('cajera');
    }
}