<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para transformación de Categorías en respuestas JSON.
 */
class CategoriaResource extends JsonResource
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
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'color' => $this->color,
            'icono' => $this->icono,
            'es_personal' => $this->es_personal,
            'tareas_count' => $this->whenCounted('tareas'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
