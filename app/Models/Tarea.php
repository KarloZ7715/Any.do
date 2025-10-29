<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Tarea extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'tareas';

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'fecha_completada',
        'fecha_vencimiento',
        'usuario_id',
        'categoria_id',
    ];

    /**
     * Obtener los atributos que deben ser convertidos.
     */
    protected function casts(): array
    {
        return [
            'fecha_completada' => 'datetime',
            'fecha_vencimiento' => 'date',
            'prioridad' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Obtener el usuario propietario de la tarea.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Obtener la categoría de la tarea.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Obtener las subtareas de la tarea.
     */
    public function subtareas(): HasMany
    {
        return $this->hasMany(Subtarea::class, 'tarea_id');
    }

    /**
     * Verificar si la tarea está completada.
     */
    public function estaCompletada(): bool
    {
        return $this->estado === 'completada';
    }

    /**
     * Verificar si la tarea está pendiente.
     */
    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Verificar si la tarea está vencida.
     */
    public function estaVencida(): bool
    {
        return $this->estaPendiente()
            && $this->fecha_vencimiento
            && $this->fecha_vencimiento < today();
    }

    /**
     * Verificar si la tarea es de alta prioridad.
     */
    public function esAltaPrioridad(): bool
    {
        return $this->prioridad === 1;
    }

    /**
     * Marcar la tarea como completada.
     */
    public function completar(): void
    {
        $this->update([
            'estado' => 'completada',
            'fecha_completada' => now(),
        ]);
    }

    /**
     * Marcar la tarea como pendiente.
     */
    public function marcarPendiente(): void
    {
        $this->update([
            'estado' => 'pendiente',
            'fecha_completada' => null,
        ]);
    }

    /**
     * Configurar qué atributos deben ser indexados para búsqueda.
     */
    public function toSearchableArray(): array
    {
        return [
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
        ];
    }

    /**
     * Scope para filtrar tareas pendientes.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para filtrar tareas completadas.
     */
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    /**
     * Scope para filtrar por prioridad.
     */
    public function scopePorPrioridad($query, int $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Scope para filtrar tareas vencidas.
     */
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'pendiente')
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now());
    }
}
