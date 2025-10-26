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
     */
    public function obtenerTareasFiltradas(
        ?int $usuarioId = null,
        ?string $estado = null,
        ?int $categoriaId = null,
        ?int $prioridad = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->tareaRepository->filtrarTareas(
            usuarioId: $usuarioId,
            estado: $estado,
            categoriaId: $categoriaId,
            prioridad: $prioridad,
            perPage: $perPage
        );
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
     * Completar una tarea.
     */
    public function completarTarea(Tarea $tarea): Tarea
    {
        $tarea->completar();
        return $tarea->fresh();
    }

    /**
     * Eliminar una tarea (soft delete).
     */
    public function eliminarTarea(Tarea $tarea): bool
    {
        return $this->tareaRepository->eliminar($tarea);
    }

    /**
     * Obtener tareas vencidas de un usuario.
     */
    public function obtenerTareasVencidas(int $usuarioId): Collection
    {
        return $this->tareaRepository->tareasVencidasPorUsuario($usuarioId);
    }

    /**
     * Obtener estadísticas de tareas de un usuario.
     */
    public function obtenerEstadisticas(int $usuarioId): array
    {
        $tareas = $this->tareaRepository->tareasPorUsuario($usuarioId);

        return [
            'total' => $tareas->count(),
            'pendientes' => $tareas->where('estado', 'pendiente')->count(),
            'completadas' => $tareas->where('estado', 'completada')->count(),
            'vencidas' => $tareas->filter(fn($tarea) => $tarea->estaVencida())->count(),
            'alta_prioridad' => $tareas->where('prioridad', 1)->count(),
        ];
    }

    /**
     * Buscar tareas por texto.
     */
    public function buscarTareas(string $query, ?int $usuarioId = null): Collection
    {
        return $this->tareaRepository->buscar($query, $usuarioId);
    }
}
