<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Categoria;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Verificar si el usuario es administrador.
     */
    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Boot del modelo para eventos.
     */
    protected static function booted(): void
    {
        // Crear categoría "Personal" automáticamente al crear usuario
        static::created(function (User $user) {
            Categoria::create([
                'nombre' => 'Personal',
                'descripcion' => 'Categoría personal predeterminada',
                'color' => '#3B82F6', // Azul
                'icono' => 'user',
                'usuario_id' => $user->id,
            ]);
        });
    }
}
