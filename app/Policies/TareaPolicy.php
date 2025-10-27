<?php

namespace App\Policies;

use App\Models\Tarea;
use App\Models\Usuario;

/**
 * Policy para autorización de operaciones sobre Tareas.
 * 
 * Define quién puede realizar cada acción sobre las tareas:
 * - Usuarios pueden ver/editar solo sus propias tareas
 * - Administradores pueden ver/editar todas las tareas
 */
class TareaPolicy
{
    /**
     * Determinar si el usuario puede ver el listado de tareas.
     * 
     * Todos los usuarios autenticados pueden ver el índice,
     * pero solo verán sus propias tareas (filtrado en el Repository).
     */
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    /**
     * Determinar si el usuario puede ver una tarea específica.
     * 
     * El usuario puede ver la tarea si:
     * - Es el propietario de la tarea, O
     * - Es un administrador
     */
    public function view(Usuario $usuario, Tarea $tarea): bool
    {
        return $usuario->esAdmin() || $usuario->id === $tarea->usuario_id;
    }

    /**
     * Determinar si el usuario puede crear tareas.
     * 
     * Todos los usuarios autenticados pueden crear tareas.
     */
    public function create(Usuario $usuario): bool
    {
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar una tarea.
     * 
     * El usuario puede actualizar la tarea si:
     * - Es el propietario de la tarea, O
     * - Es un administrador
     */
    public function update(Usuario $usuario, Tarea $tarea): bool
    {
        return $usuario->esAdmin() || $usuario->id === $tarea->usuario_id;
    }

    /**
     * Determinar si el usuario puede eliminar una tarea.
     * 
     * El usuario puede eliminar la tarea si:
     * - Es el propietario de la tarea, O
     * - Es un administrador
     */
    public function delete(Usuario $usuario, Tarea $tarea): bool
    {
        return $usuario->esAdmin() || $usuario->id === $tarea->usuario_id;
    }

    /**
     * Determinar si el usuario puede restaurar una tarea eliminada.
     * 
     * Solo los administradores pueden restaurar tareas.
     */
    public function restore(Usuario $usuario, Tarea $tarea): bool
    {
        return $usuario->esAdmin();
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente una tarea.
     * 
     * Solo los administradores pueden hacer eliminación permanente.
     */
    public function forceDelete(Usuario $usuario, Tarea $tarea): bool
    {
        return $usuario->esAdmin();
    }
}
