<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para transformación de Tareas en respuestas JSON.
 * 
 * Usado en respuestas Inertia para formatear datos de manera consistente
 * con fechas en formato ISO, relaciones cargadas cuando disponibles,
 * y campos calculados como estado y prioridad en texto.
 */
class TareaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'estado_texto' => $this->estado === 'pendiente' ? 'Pendiente' : 'Completada',
            'prioridad' => $this->prioridad,
            'prioridad_texto' => $this->obtenerPrioridadTexto(),
            'prioridad_color' => $this->obtenerPrioridadColor(),
            'fecha_vencimiento' => $this->fecha_vencimiento?->format('Y-m-d'),
            'fecha_vencimiento_humana' => $this->fecha_vencimiento?->diffForHumans(),
            'fecha_completada' => $this->fecha_completada?->format('Y-m-d H:i:s'),
            'esta_vencida' => $this->estaVencida(),
            'esta_completada' => $this->estaCompletada(),
            'dias_hasta_vencimiento' => $this->calcularDiasHastaVencimiento(),

            // Foreign Keys
            'categoria_id' => $this->categoria_id,
            'usuario_id' => $this->usuario_id,

            // Relaciones (solo si están cargadas)
            'categoria' => CategoriaResource::make($this->whenLoaded('categoria')),
            'usuario' => UsuarioResource::make($this->whenLoaded('usuario')),
            'comentarios_count' => $this->whenCounted('comentarios'),

            // Timestamps
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Obtener texto de prioridad.
     */
    private function obtenerPrioridadTexto(): string
    {
        return match ($this->prioridad) {
            1 => 'Alta',
            2 => 'Media',
            3 => 'Baja',
            default => 'Desconocida',
        };
    }

    /**
     * Obtener color de prioridad para UI.
     */
    private function obtenerPrioridadColor(): string
    {
        return match ($this->prioridad) {
            1 => 'red',    // Alta - rojo
            2 => 'yellow', // Media - amarillo
            3 => 'green',  // Baja - verde
            default => 'gray',
        };
    }

    /**
     * Calcular días hasta vencimiento.
     * Retorna null si no hay fecha de vencimiento.
     */
    private function calcularDiasHastaVencimiento(): ?int
    {
        if (!$this->fecha_vencimiento) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->fecha_vencimiento->startOfDay(), false);
    }
}
