<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'categorias';

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'icono',
        'usuario_id',
        'es_personal',
    ];

    /**
     * Obtener los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'es_personal' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Obtener las tareas de la categoría.
     */
    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'categoria_id');
    }

    /**
     * Obtener el usuario propietario de la categoría.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Obtener el número total de tareas en esta categoría.
     */
    public function totalTareas(): int
    {
        return $this->tareas()->count();
    }

    /**
     * Obtener el número de tareas pendientes en esta categoría.
     */
    public function totalTareasPendientes(): int
    {
        return $this->tareas()->where('estado', 'pendiente')->count();
    }
}
