<?php

namespace App\Actions;

use App\Models\Tarea;
use Illuminate\Support\Facades\DB;

/**
 * Action para completar una tarea con lógica adicional.
 * 
 * Las Actions son clases de una sola responsabilidad que encapsulan
 * operaciones específicas del negocio (Single Action Classes).
 */
class CompletarTareaAction
{
    /**
     * Completar una tarea y registrar el evento.
     */
    public function execute(Tarea $tarea, int $usuarioId): Tarea
    {
        // Verificar que la tarea pertenezca al usuario
        if ($tarea->usuario_id !== $usuarioId) {
            abort(403, 'No tienes permiso para completar esta tarea.');
        }

        // Verificar que la tarea no esté ya completada
        if ($tarea->estaCompletada()) {
            return $tarea;
        }

        DB::transaction(function () use ($tarea) {
            // Completar la tarea
            $tarea->completar();

            // Aquí podrías agregar lógica adicional como:
            // - Enviar notificaciones
            // - Registrar en un log de actividades
            // - Actualizar estadísticas del usuario
            // - Trigger eventos del sistema
        });

        return $tarea->fresh(['usuario', 'categoria', 'comentarios']);
    }
}
