<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

/**
 * Data Transfer Object para Tareas.
 * 
 * Los DTOs con Spatie Data proporcionan:
 * - Validación automática
 * - Type safety
 * - Transformación de datos
 * - Documentación clara de la estructura
 */
class TareaData extends Data
{
    public function __construct(
        public string $titulo,
        public ?string $descripcion,
        public string $estado,
        public int $prioridad,
        public ?int $orden,
        public ?string $fecha_vencimiento,
        public ?string $fecha_completada,
        public int $usuario_id,
        public int $categoria_id,
        public int|Optional $id = new Optional(),
    ) {
    }    /**
         * Reglas de validación personalizadas.
         * 
         * Nota: usuario_id NO se valida aquí porque se agrega automáticamente
         * en el Controller usando auth()->id()
         */
    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'estado' => ['required', 'in:pendiente,completada'],
            'prioridad' => ['required', 'integer', 'min:1', 'max:3'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:today'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    public static function messages(...$args): array
    {
        return [
            'titulo.required' => 'El título de la tarea es obligatorio',
            'titulo.max' => 'El título no puede exceder 200 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 5000 caracteres',
            'estado.required' => 'El estado de la tarea es obligatorio',
            'estado.in' => 'El estado debe ser pendiente o completada',
            'prioridad.required' => 'La prioridad es obligatoria',
            'prioridad.min' => 'La prioridad debe estar entre 1 (alta), 2 (media) y 3 (baja)',
            'prioridad.max' => 'La prioridad debe estar entre 1 (alta), 2 (media) y 3 (baja)',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy',
            'categoria_id.required' => 'Debe seleccionar una categoría',
            'categoria_id.exists' => 'La categoría especificada no existe',
        ];
    }
}
