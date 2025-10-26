<?php

namespace App\Repositories;

use App\Models\Tarea;
use App\QueryBuilders\TareaQueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repositorio para gestionar el acceso a datos de Tareas.
 * 
 * Los Repositories encapsulan la lógica de acceso a datos y queries complejas,
 * manteniendo los controladores y servicios limpios.
 */
class TareaRepository
{
    /**
     * Filtrar tareas con múltiples criterios.
     */
    public function filtrarTareas(
        ?int $usuarioId = null,
        ?string $estado = null,
        ?int $categoriaId = null,
        ?int $prioridad = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Tarea::query()
            ->with(['usuario', 'categoria', 'comentarios']);

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($prioridad) {
            $query->where('prioridad', $prioridad);
        }

        return $query->orderBy('prioridad', 'asc')
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate($perPage);
    }

    /**
     * Crear una nueva tarea.
     */
    public function crear(array $data): Tarea
    {
        return Tarea::create($data);
    }

    /**
     * Actualizar una tarea existente.
     */
    public function actualizar(Tarea $tarea, array $data): Tarea
    {
        $tarea->update($data);
        return $tarea->fresh();
    }

    /**
     * Eliminar una tarea (soft delete).
     */
    public function eliminar(Tarea $tarea): bool
    {
        return $tarea->delete();
    }

    /**
     * Obtener todas las tareas de un usuario.
     */
    public function tareasPorUsuario(int $usuarioId): Collection
    {
        return Tarea::where('usuario_id', $usuarioId)
            ->with(['categoria', 'comentarios'])
            ->get();
    }

    /**
     * Obtener tareas vencidas de un usuario.
     */
    public function tareasVencidasPorUsuario(int $usuarioId): Collection
    {
        return Tarea::where('usuario_id', $usuarioId)
            ->vencidas()
            ->with(['categoria'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }

    /**
     * Buscar tareas por texto usando Scout.
     */
    public function buscar(string $query, ?int $usuarioId = null): Collection
    {
        $results = Tarea::search($query)->get();

        if ($usuarioId) {
            $results = $results->where('usuario_id', $usuarioId);
        }

        return $results;
    }

    /**
     * Obtener tareas por categoría.
     */
    public function tareasPorCategoria(int $categoriaId): Collection
    {
        return Tarea::where('categoria_id', $categoriaId)
            ->with(['usuario', 'comentarios'])
            ->orderBy('prioridad', 'asc')
            ->get();
    }

    /**
     * Contar tareas por estado.
     */
    public function contarPorEstado(int $usuarioId): array
    {
        return [
            'pendientes' => Tarea::where('usuario_id', $usuarioId)
                ->where('estado', 'pendiente')
                ->count(),
            'completadas' => Tarea::where('usuario_id', $usuarioId)
                ->where('estado', 'completada')
                ->count(),
        ];
    }
}
