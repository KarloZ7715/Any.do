<?php

namespace App\Repositories;

use App\Models\Categoria;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Collection;

class CategoriaRepository
{
    /**
     * Obtener todas las categorías de un usuario.
     *
     * @param int $usuarioId
     * @return Collection
     */
    public function obtenerPorUsuario(int $usuarioId): Collection
    {
        return Categoria::where('usuario_id', $usuarioId)
            ->withCount('tareas')
            ->orderByRaw('es_personal DESC') // Personal primero
            ->orderBy('nombre', 'asc')
            ->get();
    }

    /**
     * Crear una nueva categoría.
     *
     * @param array $datos
     * @return Categoria
     */
    public function crear(array $datos): Categoria
    {
        return Categoria::create($datos);
    }

    /**
     * Actualizar una categoría existente.
     *
     * @param Categoria $categoria
     * @param array $datos
     * @return bool
     */
    public function actualizar(Categoria $categoria, array $datos): bool
    {
        return $categoria->update($datos);
    }

    /**
     * Eliminar una categoría.
     *
     * @param Categoria $categoria
     * @return bool|null
     */
    public function eliminar(Categoria $categoria): ?bool
    {
        return $categoria->delete();
    }

    /**
     * Contar tareas de una categoría.
     *
     * @param int $categoriaId
     * @return int
     */
    public function contarTareas(int $categoriaId): int
    {
        return Tarea::where('categoria_id', $categoriaId)->count();
    }

    /**
     * Mover todas las tareas de una categoría a otra.
     *
     * @param int $categoriaOrigenId
     * @param int $categoriaDestinoId
     * @return int Número de tareas movidas
     */
    public function moverTareas(int $categoriaOrigenId, int $categoriaDestinoId): int
    {
        return Tarea::where('categoria_id', $categoriaOrigenId)
            ->update(['categoria_id' => $categoriaDestinoId]);
    }

    /**
     * Obtener la categoría "Personal" de un usuario.
     *
     * @param int $usuarioId
     * @return Categoria|null
     */
    public function obtenerCategoriaPersonal(int $usuarioId): ?Categoria
    {
        return Categoria::where('usuario_id', $usuarioId)
            ->where('es_personal', true)
            ->first();
    }

    /**
     * Verificar si un nombre de categoría ya existe para el usuario.
     *
     * @param int $usuarioId
     * @param string $nombre
     * @param int|null $categoriaId ID de la categoría a excluir (para edición)
     * @return bool
     */
    public function nombreExiste(int $usuarioId, string $nombre, ?int $categoriaId = null): bool
    {
        $query = Categoria::where('usuario_id', $usuarioId)
            ->where('nombre', $nombre);

        if ($categoriaId) {
            $query->where('id', '!=', $categoriaId);
        }

        return $query->exists();
    }

    /**
     * Buscar categoría por ID con conteo de tareas.
     *
     * @param int $categoriaId
     * @return Categoria|null
     */
    public function buscarConTareas(int $categoriaId): ?Categoria
    {
        return Categoria::withCount('tareas')->find($categoriaId);
    }
}
