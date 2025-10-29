<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\Usuario;

class CategoriaPolicy
{
    /**
     * Determinar si el usuario puede ver cualquier categoría.
     */
    public function viewAny(Usuario $user): bool
    {
        return true; // Todos los usuarios autenticados pueden ver sus categorías
    }

    /**
     * Determinar si el usuario puede ver la categoría.
     */
    public function view(Usuario $user, Categoria $categoria): bool
    {
        return $user->id === $categoria->usuario_id;
    }

    /**
     * Determinar si el usuario puede crear categorías.
     */
    public function create(Usuario $user): bool
    {
        return true; // Todos los usuarios pueden crear categorías
    }

    /**
     * Determinar si el usuario puede actualizar la categoría.
     */
    public function update(Usuario $user, Categoria $categoria): bool
    {
        return $user->id === $categoria->usuario_id;
    }

    /**
     * Determinar si el usuario puede eliminar la categoría.
     */
    public function delete(Usuario $user, Categoria $categoria): bool
    {
        // No puede eliminar si:
        // 1. No es el propietario
        if ($user->id !== $categoria->usuario_id) {
            return false;
        }

        // 2. Es la categoría Personal
        if ($categoria->es_personal) {
            return false;
        }

        return true;
    }

    /**
     * Determinar si el usuario puede restaurar la categoría.
     */
    public function restore(Usuario $user, Categoria $categoria): bool
    {
        return $user->id === $categoria->usuario_id;
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente la categoría.
     */
    public function forceDelete(Usuario $user, Categoria $categoria): bool
    {
        // No permitir force delete de categoría Personal
        if ($categoria->es_personal) {
            return false;
        }

        return $user->id === $categoria->usuario_id;
    }
}
