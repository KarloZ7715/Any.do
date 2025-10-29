<?php

namespace App\Http\Controllers;

use App\Data\SubtareaData;
use App\Models\Subtarea;
use App\Models\Tarea;
use App\Services\SubtareaService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controlador para gestión de Subtareas.
 * 
 * Patrón Thin Controller: solo coordinación HTTP,
 * toda la lógica de negocio está en SubtareaService.
 */
class SubtareaController extends Controller
{
    public function __construct(
        private readonly SubtareaService $subtareaService
    ) {
    }

    /**
     * Crear una nueva subtarea para una tarea.
     */
    public function store(Request $request, Tarea $tarea): RedirectResponse
    {
        // Verificar que el usuario puede modificar la tarea
        $this->authorize('update', $tarea);

        try {
            $data = SubtareaData::from($request->all());
            $this->subtareaService->crearSubtarea($data);

            return back()->with('success', 'Subtarea creada exitosamente');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Actualizar una subtarea existente.
     */
    public function update(Request $request, Subtarea $subtarea): RedirectResponse
    {
        $this->authorize('update', $subtarea);

        try {
            $data = SubtareaData::from($request->all());
            $this->subtareaService->actualizarSubtarea($subtarea, $data);

            return back()->with('success', 'Subtarea actualizada');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Eliminar una subtarea.
     */
    public function destroy(Subtarea $subtarea): RedirectResponse
    {
        $this->authorize('delete', $subtarea);

        try {
            $this->subtareaService->eliminarSubtarea($subtarea);

            return back()->with('success', 'Subtarea eliminada');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Alternar estado de una subtarea (pendiente ↔ completada).
     */
    public function toggle(Subtarea $subtarea): RedirectResponse
    {
        $this->authorize('update', $subtarea);

        try {
            $this->subtareaService->cambiarEstado($subtarea);

            return back()->with('success', 'Estado actualizado');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
