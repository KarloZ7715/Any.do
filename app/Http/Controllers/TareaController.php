<?php

namespace App\Http\Controllers;

use App\Data\TareaData;
use App\Http\Resources\CategoriaResource;
use App\Http\Resources\TareaResource;
use App\Models\Categoria;
use App\Models\Tarea;
use App\Services\TareaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador para gestión de Tareas.
 * 
 * Patrón Thin Controller: solo coordinación HTTP,
 * toda la lógica de negocio está en TareaService.
 */
class TareaController extends Controller
{
    public function __construct(
        private readonly TareaService $tareaService
    ) {
    }

    /**
     * Mostrar listado de tareas con filtros.
     * 
     * Los usuarios normales solo ven sus tareas.
     * Los administradores pueden ver todas las tareas.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tarea::class);

        $filtros = $request->only([
            'estado',
            'prioridad',
            'categoria_id',
            'vencimiento',
            'buscar',
            'ordenar',
            'direccion',
        ]);

        // Si no es admin, forzar filtro de usuario_id
        if (!auth()->user()->esAdmin()) {
            $filtros['usuario_id'] = auth()->id();
        }

        $perPage = $request->input('per_page', 15);

        $tareas = $this->tareaService->obtenerTareasFiltradas($filtros, $perPage);

        // Obtener categorías del usuario para el selector
        $categorias = Categoria::where('usuario_id', auth()->id())
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Tareas/Index', [
            'tareas' => [
                'data' => TareaResource::collection($tareas->items())->resolve(),
                'links' => $tareas->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $tareas->currentPage(),
                    'last_page' => $tareas->lastPage(),
                    'per_page' => $tareas->perPage(),
                    'total' => $tareas->total(),
                    'from' => $tareas->firstItem(),
                    'to' => $tareas->lastItem(),
                ],
            ],
            'filtros' => $filtros,
            'categorias' => $categorias,
        ]);
    }

    /**
     * Crear una nueva tarea.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Tarea::class);

        // Validar request antes de crear TareaData
        $validated = $request->validate(TareaData::rules());

        // Combinar fecha y hora si viene hora_vencimiento
        if ($request->has('hora_vencimiento') && $validated['fecha_vencimiento']) {
            $hora = $request->input('hora_vencimiento');
            if ($hora) {
                // Combinar fecha y hora en timezone local
                $fechaHora = $validated['fecha_vencimiento'] . ' ' . $hora . ':00';
                $validated['fecha_vencimiento'] = \Carbon\Carbon::parse($fechaHora, config('app.timezone'));
            } else {
                // Solo fecha, usar medianoche en timezone local
                $validated['fecha_vencimiento'] = \Carbon\Carbon::parse($validated['fecha_vencimiento'], config('app.timezone'))
                    ->startOfDay();
            }
        } elseif ($validated['fecha_vencimiento']) {
            // Solo fecha sin hora, usar medianoche en timezone local
            $validated['fecha_vencimiento'] = \Carbon\Carbon::parse($validated['fecha_vencimiento'], config('app.timezone'))
                ->startOfDay();
        }

        // Si no se seleccionó categoría, usar categoría "Personal" del usuario
        if (empty($validated['categoria_id'])) {
            $categoriaPersonal = Categoria::where('usuario_id', auth()->id())
                ->where('nombre', 'Personal')
                ->first();

            $validated['categoria_id'] = $categoriaPersonal?->id;
        }

        $data = TareaData::from([
            ...$validated,
            'usuario_id' => auth()->id(),
        ]);

        $tarea = $this->tareaService->crearTarea($data);

        return back()->with('success', 'Tarea creada exitosamente');
    }

    /**
     * Actualizar una tarea existente.
     */
    public function update(Request $request, Tarea $tarea): RedirectResponse
    {
        $this->authorize('update', $tarea);

        // Si solo se envía fecha_vencimiento (desde drag & drop), actualizar directamente
        if ($request->has('fecha_vencimiento') && count($request->all()) === 1) {
            $request->validate([
                'fecha_vencimiento' => ['required', 'date'],
            ]);

            // Parsear la fecha como medianoche en timezone local (America/Bogota)
            $fecha = \Carbon\Carbon::parse($request->input('fecha_vencimiento'), config('app.timezone'))
                ->startOfDay();

            $tarea->update([
                'fecha_vencimiento' => $fecha,
            ]);

            return back()->with('success', 'Fecha de tarea actualizada');
        }

        // Actualización completa: validar todos los campos
        $validated = $request->validate(TareaData::rules());

        // Si no viene categoria_id en el request, mantener la categoría actual
        if (!$request->has('categoria_id')) {
            $validated['categoria_id'] = $tarea->categoria_id;
        }

        // Combinar fecha y hora si viene hora_vencimiento
        if ($request->has('hora_vencimiento') && $validated['fecha_vencimiento']) {
            $hora = $request->input('hora_vencimiento');
            if ($hora) {
                // Combinar fecha y hora en timezone local
                $fechaHora = $validated['fecha_vencimiento'] . ' ' . $hora . ':00';
                $validated['fecha_vencimiento'] = \Carbon\Carbon::parse($fechaHora, config('app.timezone'));
            } else {
                // Solo fecha, usar medianoche en timezone local
                $validated['fecha_vencimiento'] = \Carbon\Carbon::parse($validated['fecha_vencimiento'], config('app.timezone'))
                    ->startOfDay();
            }
        } elseif ($validated['fecha_vencimiento']) {
            // Solo fecha sin hora, usar medianoche en timezone local
            $validated['fecha_vencimiento'] = \Carbon\Carbon::parse($validated['fecha_vencimiento'], config('app.timezone'))
                ->startOfDay();
        }

        $data = TareaData::from([
            ...$validated,
            'id' => $tarea->id,
            'usuario_id' => $tarea->usuario_id, // No cambiar propietario
        ]);

        $this->tareaService->actualizarTarea($tarea, $data);

        return back()->with('success', 'Tarea actualizada exitosamente');
    }

    /**
     * Eliminar una tarea (soft delete).
     */
    public function destroy(Tarea $tarea): RedirectResponse
    {
        $this->authorize('delete', $tarea);

        $this->tareaService->eliminarTarea($tarea);

        return back()->with('success', 'Tarea eliminada exitosamente');
    }

    /**
     * Alternar estado de una tarea (pendiente <-> completada).
     */
    public function toggle(Tarea $tarea): RedirectResponse
    {
        $this->authorize('update', $tarea);

        $tareaActualizada = $this->tareaService->toggleEstado($tarea);

        $mensaje = $tareaActualizada->estaCompletada()
            ? 'Tarea marcada como completada'
            : 'Tarea marcada como pendiente';

        return back()->with('success', $mensaje);
    }

    /**
     * Vista: Próximos 7 Días.
     * 
     * Tareas con fecha de vencimiento entre hoy y los próximos 7 días,
     * agrupadas por fecha.
     */
    public function proximosSieteDias(Request $request): Response
    {
        $this->authorize('viewAny', Tarea::class);

        $tareas = $this->tareaService->obtenerTareasProximos7Dias(auth()->id());

        // Obtener categorías del usuario para el selector
        $categorias = Categoria::where('usuario_id', auth()->id())
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Tareas/Proximos7Dias', [
            'tareasPorFecha' => $tareas,
            'categorias' => CategoriaResource::collection($categorias)->resolve(),
        ]);
    }

    /**
     * Vista: Todas mis Tareas.
     * 
     * Listado completo de tareas (pendientes y completadas)
     * con filtros avanzados y ordenamiento personalizado.
     */
    public function todasMisTareas(Request $request): Response
    {
        $this->authorize('viewAny', Tarea::class);

        // Obtener filtro de categoría si existe
        $categoriaId = $request->input('categoria_id');

        // Query base: tareas del usuario
        $query = Tarea::where('usuario_id', auth()->id())
            ->with(['categoria', 'subtareas']);

        // Aplicar filtro de categoría si se proporciona
        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        // Ordenar: pendientes primero, luego por fecha de creación
        $tareas = $query
            ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener categorías del usuario
        $categorias = Categoria::where('usuario_id', auth()->id())
            ->orderBy('nombre')
            ->get();

        // Obtener la categoría seleccionada si existe
        $categoriaSeleccionada = null;
        if ($categoriaId) {
            $categoriaSeleccionada = Categoria::where('id', $categoriaId)
                ->where('usuario_id', auth()->id())
                ->first();
        }

        return Inertia::render('Tareas/TodasLasTareas', [
            'tareas' => TareaResource::collection($tareas)->resolve(),
            'categorias' => CategoriaResource::collection($categorias)->resolve(),
            'categoriaSeleccionada' => $categoriaSeleccionada ? CategoriaResource::make($categoriaSeleccionada)->resolve() : null,
        ]);
    }

    /**
     * Vista: Mi Calendario.
     * 
     * Vista de calendario mensual con tareas organizadas por día.
     */
    public function miCalendario(Request $request): Response
    {
        $this->authorize('viewAny', Tarea::class);

        $mes = (int) $request->input('mes', now()->month);
        $anio = (int) $request->input('anio', now()->year);

        $tareasPorDia = $this->tareaService->obtenerTareasPorCalendario(auth()->id(), $mes, $anio);

        // Obtener categorías del usuario
        $categorias = Categoria::where('usuario_id', auth()->id())
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Tareas/MiCalendario', [
            'tareasPorDia' => $tareasPorDia,
            'mes' => $mes,
            'anio' => $anio,
            'categorias' => CategoriaResource::collection($categorias)->resolve(),
        ]);
    }
}
