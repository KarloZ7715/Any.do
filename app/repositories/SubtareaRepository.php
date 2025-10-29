<?php

namespace App\Repositories;

use App\Models\Subtarea;
use Illuminate\Support\Collection;

/**
 * Repositorio para gestionar el acceso a datos de Subtareas.
 * 
 * Maneja queries y operaciones de base de datos relacionadas con subtareas.
 */
class SubtareaRepository
{
    /**
     * Obtener todas las subtareas de una tarea específica.
     * 
     * @param int $tareaId ID de la tarea
     * @return Collection
     */
    public function obtenerPorTarea(int $tareaId): Collection
    {
        return Subtarea::where('tarea_id', $tareaId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Crear una nueva subtarea.
     * 
     * @param array $data Datos de la subtarea
     * @return Subtarea
     */
    public function crear(array $data): Subtarea
    {
        return Subtarea::create($data);
    }

    /**
     * Actualizar una subtarea existente.
     * 
     * @param Subtarea $subtarea Instancia de la subtarea
     * @param array $data Datos a actualizar
     * @return Subtarea
     */
    public function actualizar(Subtarea $subtarea, array $data): Subtarea
    {
        $subtarea->update($data);
        return $subtarea->fresh();
    }

    /**
     * Eliminar una subtarea (soft delete).
     * 
     * @param Subtarea $subtarea Instancia de la subtarea
     * @return bool
     */
    public function eliminar(Subtarea $subtarea): bool
    {
        return $subtarea->delete();
    }

    /**
     * Alternar el estado de una subtarea (pendiente ↔ completada).
     * 
     * @param Subtarea $subtarea Instancia de la subtarea
     * @return Subtarea
     */
    public function toggleEstado(Subtarea $subtarea): Subtarea
    {
        $subtarea->toggleEstado();
        return $subtarea->fresh();
    }

    /**
     * Contar subtareas de una tarea específica.
     * 
     * @param int $tareaId ID de la tarea
     * @return int
     */
    public function contarPorTarea(int $tareaId): int
    {
        // No incluir soft-deleted (withTrashed excluido por defecto)
        return Subtarea::where('tarea_id', $tareaId)->count();
    }

    /**
     * Validar si se puede agregar una subtarea más (máximo 30).
     * 
     * @param int $tareaId ID de la tarea
     * @return bool True si puede agregar más subtareas
     */
    public function validarLimite(int $tareaId): bool
    {
        return $this->contarPorTarea($tareaId) < 30;
    }

    /**
     * Contar subtareas completadas de una tarea.
     * 
     * @param int $tareaId ID de la tarea
     * @return int
     */
    public function contarCompletadas(int $tareaId): int
    {
        return Subtarea::where('tarea_id', $tareaId)
            ->where('estado', 'completada')
            ->count();
    }

    /**
     * Contar subtareas pendientes de una tarea.
     * 
     * @param int $tareaId ID de la tarea
     * @return int
     */
    public function contarPendientes(int $tareaId): int
    {
        return Subtarea::where('tarea_id', $tareaId)
            ->where('estado', 'pendiente')
            ->count();
    }
}
