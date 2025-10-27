<?php

namespace App\Services;

use App\Data\TareaData;
use App\Models\Tarea;
use App\Repositories\TareaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Servicio para gestionar la lógica de negocio de Tareas.
 * 
 * Los Services contienen la lógica de negocio compleja y coordinan
 * múltiples repositorios, actions o servicios externos.
 */
class TareaService
{
    public function __construct(
        private TareaRepository $tareaRepository
    ) {
    }

    /**
     * Obtener tareas con filtros aplicados.
     * 
     * @param array $filtros Filtros: usuario_id, estado, prioridad, categoria_id, vencimiento, buscar, ordenar, direccion
     * @param int $perPage Tareas por página
     * @return LengthAwarePaginator
     */
    public function obtenerTareasFiltradas(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tareaRepository->filtrarTareas($filtros, $perPage);
    }

    /**
     * Crear una nueva tarea.
     */
    public function crearTarea(TareaData $data): Tarea
    {
        return $this->tareaRepository->crear($data->toArray());
    }

    /**
     * Actualizar una tarea existente.
     */
    public function actualizarTarea(Tarea $tarea, TareaData $data): Tarea
    {
        return $this->tareaRepository->actualizar($tarea, $data->toArray());
    }

    /**
     * Eliminar una tarea (soft delete).
     */
    public function eliminarTarea(Tarea $tarea): bool
    {
        return $this->tareaRepository->eliminar($tarea);
    }

    /**
     * Alternar estado de una tarea (pendiente <-> completada).
     */
    public function toggleEstado(Tarea $tarea): Tarea
    {
        return $this->tareaRepository->toggleEstado($tarea);
    }

    /**
     * Obtener tareas vencidas de un usuario.
     */
    public function obtenerTareasVencidas(int $usuarioId): Collection
    {
        return $this->tareaRepository->tareasVencidasPorUsuario($usuarioId);
    }

    /**
     * Obtener tareas para los próximos 7 días.
     */
    public function obtenerTareas7Dias(int $usuarioId): Collection
    {
        return $this->tareaRepository->tareas7Dias($usuarioId);
    }

    /**
     * Obtener tareas por categoría.
     */
    public function obtenerTareasPorCategoria(int $categoriaId, ?int $usuarioId = null): Collection
    {
        return $this->tareaRepository->tareasPorCategoria($categoriaId, $usuarioId);
    }

    /**
     * Obtener estadísticas de tareas de un usuario.
     */
    public function obtenerEstadisticas(int $usuarioId): array
    {
        return $this->tareaRepository->contarPorEstado($usuarioId);
    }
}
