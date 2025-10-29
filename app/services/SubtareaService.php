<?php

namespace App\Services;

use App\Data\SubtareaData;
use App\Models\Subtarea;
use App\Repositories\SubtareaRepository;
use Exception;

/**
 * Servicio para la lógica de negocio de Subtareas.
 * 
 * Coordina operaciones complejas y valida reglas de negocio.
 */
class SubtareaService
{
    public function __construct(
        protected SubtareaRepository $subtareaRepository
    ) {
    }

    /**
     * Crear una nueva subtarea validando el límite de 30.
     * 
     * @param SubtareaData $data Datos validados de la subtarea
     * @return Subtarea
     * @throws Exception Si se excede el límite de 30 subtareas
     */
    public function crearSubtarea(SubtareaData $data): Subtarea
    {
        // Validar límite de 30 subtareas por tarea
        if (!$this->subtareaRepository->validarLimite($data->tarea_id)) {
            throw new Exception('No se pueden agregar más de 30 subtareas por tarea');
        }

        return $this->subtareaRepository->crear($data->toArray());
    }

    /**
     * Actualizar una subtarea existente.
     * 
     * @param Subtarea $subtarea Instancia de la subtarea
     * @param SubtareaData $data Datos a actualizar
     * @return Subtarea
     */
    public function actualizarSubtarea(Subtarea $subtarea, SubtareaData $data): Subtarea
    {
        return $this->subtareaRepository->actualizar($subtarea, $data->toArray());
    }

    /**
     * Eliminar una subtarea.
     * 
     * @param Subtarea $subtarea Instancia de la subtarea
     * @return bool
     */
    public function eliminarSubtarea(Subtarea $subtarea): bool
    {
        return $this->subtareaRepository->eliminar($subtarea);
    }

    /**
     * Cambiar el estado de una subtarea (pendiente ↔ completada).
     * 
     * @param Subtarea $subtarea Instancia de la subtarea
     * @return Subtarea
     */
    public function cambiarEstado(Subtarea $subtarea): Subtarea
    {
        return $this->subtareaRepository->toggleEstado($subtarea);
    }
}
