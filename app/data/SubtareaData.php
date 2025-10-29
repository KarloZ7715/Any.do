<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Data;

class SubtareaData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $texto,

        #[Required, Exists('tareas', 'id')]
        public int $tarea_id,

        #[In(['pendiente', 'completada'])]
        public ?string $estado = 'pendiente',
    ) {
    }

    /**
     * Mensajes de validación personalizados en español.
     */
    public static function messages(...$args): array
    {
        return [
            'texto.required' => 'El texto de la subtarea es obligatorio',
            'texto.max' => 'El texto no puede exceder los 255 caracteres',
            'tarea_id.required' => 'La tarea es obligatoria',
            'tarea_id.exists' => 'La tarea seleccionada no existe',
            'estado.in' => 'El estado debe ser pendiente o completada',
        ];
    }

    /**
     * Atributos personalizados para los mensajes de validación.
     */
    public static function attributes(...$args): array
    {
        return [
            'texto' => 'texto de la subtarea',
            'tarea_id' => 'tarea',
            'estado' => 'estado',
        ];
    }
}
