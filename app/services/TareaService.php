<?php

namespace App\Services;

use App\Data\TareaData;
use App\Models\Categoria;
use App\Models\Tarea;
use App\Repositories\TareaRepository;
use Carbon\Carbon;
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

    /**
     * Obtener tareas de los próximos 7 días agrupadas por fecha.
     * 
     * Retorna array con fechas como keys y colección de tareas como values.
     * 
     * @param int $usuarioId ID del usuario
     * @return array Array ['YYYY-MM-DD' => Collection<Tarea>]
     */
    public function obtenerTareasProximos7Dias(int $usuarioId): array
    {
        return $this->tareaRepository->tareasProximos7DiasAgrupadas($usuarioId);
    }

    /**
     * Obtener TODAS las tareas del usuario con filtros y paginación.
     * 
     * Incluye pendientes y completadas.
     * 
     * @param array $filtros Filtros aplicables
     * @param int $perPage Tareas por página
     * @return LengthAwarePaginator
     */
    public function obtenerTodasLasTareas(array $filtros = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->tareaRepository->todasLasTareas($filtros, $perPage);
    }

    /**
     * Obtener tareas organizadas por día para vista de calendario.
     * 
     * Retorna array con días del mes como keys y tareas como values.
     * 
     * @param int $usuarioId ID del usuario
     * @param int $mes Número del mes (1-12)
     * @param int $anio Año
     * @return array Array ['YYYY-MM-DD' => Collection<Tarea>]
     */
    public function obtenerTareasPorCalendario(int $usuarioId, int $mes, int $anio): array
    {
        return $this->tareaRepository->tareasPorCalendario($usuarioId, $mes, $anio);
    }

    // ========================================
    // HELPERS DE PROCESAMIENTO
    // ========================================

    /**
     * Parsear y combinar fecha con hora opcional.
     * 
     * Si hay hora, la combina con la fecha.
     * Si no hay hora, usa medianoche (00:00:00).
     * 
     * @param string|null $fecha Fecha en formato Y-m-d
     * @param string|null $hora Hora en formato H:i (opcional)
     * @return Carbon|null
     */
    public function parsearFechaConHora(?string $fecha, ?string $hora = null): ?Carbon
    {
        if (!$fecha) {
            return null;
        }

        $timezone = config('app.timezone');

        if ($hora) {
            return Carbon::parse("{$fecha} {$hora}:00", $timezone);
        }

        return Carbon::parse($fecha, $timezone)->startOfDay();
    }

    /**
     * Actualizar fecha preservando hora existente.
     * 
     * Usado principalmente para drag & drop en calendario.
     * 
     * @param string $nuevaFecha Nueva fecha en formato Y-m-d
     * @param Carbon|null $fechaExistente Fecha existente con hora a preservar
     * @param string|null $horaOverride Hora opcional que sobrescribe la existente
     * @return Carbon
     */
    public function actualizarFechaPreservandoHora(
        string $nuevaFecha,
        ?Carbon $fechaExistente = null,
        ?string $horaOverride = null
    ): Carbon {
        $timezone = config('app.timezone');
        $fecha = Carbon::parse($nuevaFecha, $timezone)->startOfDay();

        if ($horaOverride) {
            return $fecha->setTimeFromTimeString("{$horaOverride}:00");
        }

        if ($fechaExistente) {
            return $fecha->setTimeFromTimeString($fechaExistente->format('H:i:s'));
        }

        return $fecha;
    }

    /**
     * Obtener categoría "Personal" por defecto del usuario.
     * 
     * Usada cuando no se especifica categoría al crear tarea.
     * 
     * @param int $usuarioId ID del usuario
     * @return int|null ID de la categoría Personal o null si no existe
     */
    public function obtenerCategoriaPersonalId(int $usuarioId): ?int
    {
        $categoria = Categoria::where('usuario_id', $usuarioId)
            ->where('nombre', 'Personal')
            ->first();

        return $categoria?->id;
    }

    /**
     * Obtener categorías del usuario ordenadas por nombre.
     * 
     * @param int $usuarioId ID del usuario
     * @return Collection<Categoria>
     */
    public function obtenerCategoriasUsuario(int $usuarioId): Collection
    {
        return Categoria::where('usuario_id', $usuarioId)
            ->orderBy('nombre')
            ->get();
    }
}
