<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para transformación de Usuarios en respuestas JSON.
 * 
 * No incluye información sensible como contraseñas o tokens.
 */
class UsuarioResource extends JsonResource
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
            'email' => $this->email,
            'rol' => $this->rol,
            'es_admin' => $this->esAdmin(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
