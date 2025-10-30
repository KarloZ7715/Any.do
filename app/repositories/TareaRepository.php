<?php

namespace App\Repositories;

use App\Models\Tarea;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * Filtrar tareas con múltiples criterios y paginación.
     * 
     * @param array $filtros Criterios de filtrado
     * @param int $perPage Tareas por página
     * @return LengthAwarePaginator
     */
    public function filtrarTareas(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Tarea::query()->with(['usuario', 'categoria', 'subtareas']);

        // Filtro por usuario (obligatorio para usuarios normales)
        if (isset($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        // Filtro por estado: pendiente, completada, todas
        if (isset($filtros['estado']) && $filtros['estado'] !== 'todas') {
            $query->where('estado', $filtros['estado']);
        }

        // Filtro por prioridad: 1 (alta), 2 (media), 3 (baja)
        if (isset($filtros['prioridad'])) {
            $query->where('prioridad', $filtros['prioridad']);
        }

        // Filtro por categoría
        if (isset($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        // Filtro por fecha de vencimiento
        if (isset($filtros['vencimiento'])) {
            $this->aplicarFiltroVencimiento($query, $filtros['vencimiento']);
        }

        // Búsqueda con Scout (texto libre en titulo y descripcion)
        if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
            $idsEncontrados = Tarea::search($filtros['buscar'])->keys();
            $query->whereIn('id', $idsEncontrados);
        }

        // Ordenamiento
        $ordenar = $filtros['ordenar'] ?? 'prioridad';
        $direccion = $filtros['direccion'] ?? 'asc';

        match ($ordenar) {
            'prioridad' => $query->orderBy('prioridad', $direccion)->orderBy('fecha_vencimiento', 'asc'),
            'fecha_vencimiento' => $query->orderBy('fecha_vencimiento', $direccion),
            'created_at' => $query->orderBy('created_at', $direccion),
            default => $query->orderBy('prioridad', 'asc'),
        };

        return $query->paginate($perPage);
    }

    /**
     * Aplicar filtro de vencimiento según criterio.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $criterio hoy, semana, mes, vencidas
     * @return void
     */
    protected function aplicarFiltroVencimiento($query, string $criterio): void
    {
        $hoy = Carbon::today();

        match ($criterio) {
            'hoy' => $query->whereDate('fecha_vencimiento', $hoy),
            'semana' => $query->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addWeek()]),
            'mes' => $query->whereBetween('fecha_vencimiento', [$hoy, $hoy->copy()->addMonth()]),
            'vencidas' => $query->where('fecha_vencimiento', '<', $hoy)->where('estado', 'pendiente'),
            default => null,
        };
    }

    /**
     * Obtener todas las tareas de un usuario.
     */
    public function tareasPorUsuario(int $usuarioId): Collection
    {
        return Tarea::where('usuario_id', $usuarioId)
            ->with(['categoria'])
            ->orderBy('prioridad', 'asc')
            ->orderBy('fecha_vencimiento', 'asc')
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
     * Obtener tareas para los próximos 7 días.
     */
    public function tareas7Dias(int $usuarioId): Collection
    {
        return Tarea::where('usuario_id', $usuarioId)
            ->whereBetween('fecha_vencimiento', [
                Carbon::today(),
                Carbon::today()->addWeek()
            ])
            ->with(['categoria'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('prioridad', 'asc')
            ->get();
    }

    /**
     * Obtener tareas por categoría.
     */
    public function tareasPorCategoria(int $categoriaId, ?int $usuarioId = null): Collection
    {
        $query = Tarea::where('categoria_id', $categoriaId)
            ->with(['usuario']);

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }

        return $query->orderBy('prioridad', 'asc')->get();
    }

    /**
     * Contar tareas por estado para un usuario.
     */
    public function contarPorEstado(int $usuarioId): array
    {
        return [
            'pendientes' => Tarea::where('usuario_id', $usuarioId)->pendientes()->count(),
            'completadas' => Tarea::where('usuario_id', $usuarioId)->completadas()->count(),
            'vencidas' => Tarea::where('usuario_id', $usuarioId)->vencidas()->count(),
            'total' => Tarea::where('usuario_id', $usuarioId)->count(),
        ];
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
        return $tarea->fresh(['usuario', 'categoria']);
    }

    /**
     * Eliminar una tarea (soft delete).
     */
    public function eliminar(Tarea $tarea): bool
    {
        return $tarea->delete();
    }

    /**
     * Alternar estado de una tarea (pendiente <-> completada).
     */
    public function toggleEstado(Tarea $tarea): Tarea
    {
        if ($tarea->estaCompletada()) {
            $tarea->marcarPendiente();
        } else {
            $tarea->completar();
        }

        $tarea->save();
        return $tarea->fresh(['usuario', 'categoria']);
    }

    /**
     * Obtener tareas de los próximos 7 días agrupadas por fecha.
     * 
     * Retorna array con fechas como keys y colección de tareas como values.
     * Solo tareas pendientes.
     * 
     * @param int $usuarioId ID del usuario
     * @return array ['YYYY-MM-DD' => Collection<Tarea>]
     */
    public function tareasProximos7DiasAgrupadas(int $usuarioId): array
    {
        $hoy = Carbon::today();
        $sieteHoras = $hoy->copy()->addDays(7);

        $tareas = Tarea::where('usuario_id', $usuarioId)
            ->where('estado', 'pendiente')
            ->whereBetween('fecha_vencimiento', [$hoy, $sieteHoras])
            ->with(['categoria', 'subtareas'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('prioridad', 'asc')
            ->get();

        // Agrupar tareas por fecha
        return $tareas->groupBy(function ($tarea) {
            return Carbon::parse($tarea->fecha_vencimiento)->format('Y-m-d');
        })->toArray();
    }

    /**
     * Obtener TODAS las tareas del usuario con paginación.
     * 
     * Incluye pendientes y completadas.
     * Incluye ordenamiento personalizado.
     * 
     * @param array $filtros Filtros opcionales
     * @param int $perPage Tareas por página
     * @return LengthAwarePaginator
     */
    public function todasLasTareas(array $filtros = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Tarea::query()->with(['usuario', 'categoria', 'subtareas']);

        // Filtro por usuario (siempre aplicar)
        if (isset($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        // Filtros opcionales
        if (isset($filtros['estado']) && $filtros['estado'] !== 'todas') {
            $query->where('estado', $filtros['estado']);
        }

        if (isset($filtros['prioridad'])) {
            $query->where('prioridad', $filtros['prioridad']);
        }

        if (isset($filtros['categoria_id'])) {
            $query->where('categoria_id', $filtros['categoria_id']);
        }

        if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
            $idsEncontrados = Tarea::search($filtros['buscar'])->keys();
            $query->whereIn('id', $idsEncontrados);
        }

        // Ordenamiento con campo 'orden' personalizado (cuando exista)
        $ordenar = $filtros['ordenar'] ?? 'orden';
        $direccion = $filtros['direccion'] ?? 'asc';

        match ($ordenar) {
            'orden' => $query->orderBy('orden', $direccion)->orderBy('created_at', 'desc'),
            'prioridad' => $query->orderBy('prioridad', $direccion)->orderBy('fecha_vencimiento', 'asc'),
            'fecha_vencimiento' => $query->orderBy('fecha_vencimiento', $direccion),
            'created_at' => $query->orderBy('created_at', $direccion),
            default => $query->orderBy('orden', 'asc')->orderBy('created_at', 'desc'),
        };

        return $query->paginate($perPage);
    }

    /**
     * Obtener tareas organizadas por día para calendario mensual.
     * 
     * Retorna array con días del mes como keys y tareas como values.
     * Solo tareas con fecha de vencimiento en el mes especificado.
     * 
     * @param int $usuarioId ID del usuario
     * @param int $mes Número del mes (1-12)
     * @param int $anio Año
     * @return array ['YYYY-MM-DD' => Collection<Tarea>]
     */
    public function tareasPorCalendario(int $usuarioId, int $mes, int $anio): array
    {
        $inicioMes = Carbon::create($anio, $mes, 1)->startOfDay();
        $finMes = $inicioMes->copy()->endOfMonth()->endOfDay();

        $tareas = Tarea::where('usuario_id', $usuarioId)
            ->whereBetween('fecha_vencimiento', [$inicioMes, $finMes])
            ->with(['categoria', 'subtareas'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('prioridad', 'asc')
            ->get();

        // Agrupar tareas por fecha (YYYY-MM-DD)
        return $tareas->groupBy(function ($tarea) {
            return Carbon::parse($tarea->fecha_vencimiento)->format('Y-m-d');
        })->toArray();
    }
}
