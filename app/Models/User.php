<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     * Se agregaron 'rol' y 'turno' para que Laravel permita guardarlos.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',   
        'turno', 
        'foto',
    ];

    /**
     * Los atributos que deben permanecer ocultos para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser casteados.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Esto asegura que Laravel maneje bien el hashing
    ];

    /**
     * Verifica si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->rol === 'Administrador';
    }
}