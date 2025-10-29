<?php

namespace App\Policies;

use App\Models\Subtarea;
use App\Models\User;

/**
 * Policy para autorización de operaciones sobre Subtareas.
 * 
 * Define quién puede realizar cada acción sobre las subtareas:
 * - Usuarios pueden gestionar subtareas de sus propias tareas
 * - Administradores tienen acceso completo
 */
class SubtareaPolicy
{
    /**
     * Determinar si el usuario puede ver el listado de subtareas.
     * 
     * Todos los usuarios autenticados pueden ver subtareas.
     */
    public function viewAny(User $usuario): bool
    {
        return true;
    }

    /**
     * Determinar si el usuario puede ver una subtarea específica.
     * 
     * El usuario puede ver la subtarea si:
     * - Es el propietario de la tarea padre, O
     * - Es un administrador
     */
    public function view(User $usuario, Subtarea $subtarea): bool
    {
        return $usuario->esAdmin() || $usuario->id === $subtarea->tarea->usuario_id;
    }

    /**
     * Determinar si el usuario puede crear subtareas.
     * 
     * El usuario puede crear subtareas si es el dueño de la tarea padre.
     * Se verifica en el controller que la tarea pertenezca al usuario.
     */
    public function create(User $usuario): bool
    {
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar una subtarea.
     * 
     * El usuario puede actualizar la subtarea si:
     * - Es el propietario de la tarea padre, O
     * - Es un administrador
     */
    public function update(User $usuario, Subtarea $subtarea): bool
    {
        return $usuario->esAdmin() || $usuario->id === $subtarea->tarea->usuario_id;
    }

    /**
     * Determinar si el usuario puede eliminar una subtarea.
     * 
     * El usuario puede eliminar la subtarea si:
     * - Es el propietario de la tarea padre, O
     * - Es un administrador
     */
    public function delete(User $usuario, Subtarea $subtarea): bool
    {
        return $usuario->esAdmin() || $usuario->id === $subtarea->tarea->usuario_id;
    }
}
