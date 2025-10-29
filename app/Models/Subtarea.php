<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Subtarea extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'subtareas';

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'texto',
        'tarea_id',
        'estado',
    ];

    /**
     * Obtener los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'estado' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Obtener la tarea a la que pertenece la subtarea.
     */
    public function tarea(): BelongsTo
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    /**
     * Verificar si la subtarea está completada.
     */
    public function estaCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    /**
     * Verificar si la subtarea está pendiente.
     */
    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Marcar la subtarea como completada.
     */
    public function completar(): bool
    {
        return $this->update(['estado' => 'completada']);
    }

    /**
     * Marcar la subtarea como pendiente.
     */
    public function marcarPendiente(): bool
    {
        return $this->update(['estado' => 'pendiente']);
    }

    /**
     * Alternar el estado de la subtarea (pendiente ↔ completada).
     */
    public function toggleEstado(): bool
    {
        $nuevoEstado = $this->estaCompletada() ? 'pendiente' : 'completada';
        return $this->update(['estado' => $nuevoEstado]);
    }

    /**
     * Obtener los índices buscables de Scout.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'texto' => $this->texto,
            'estado' => $this->estado,
        ];
    }
}
