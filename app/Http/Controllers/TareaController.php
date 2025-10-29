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

        // Validar request antes de crear TareaData
        $validated = $request->validate(TareaData::rules());

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
}
