<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
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
        #[Required, Max(200)]
        public string $titulo,

        #[Max(65535)]
        public ?string $descripcion,

        #[Required, Enum('pendiente', 'completada')]
        public string $estado,

        #[Required, IntegerType, Min(1), Max(3)]
        public int $prioridad,

        public ?string $fecha_vencimiento,

        #[Required, IntegerType]
        public int $usuario_id,

        #[Required, IntegerType]
        public int $categoria_id,
    ) {}

    /**
     * Crear desde un request de Laravel.
     */
    public static function fromRequest($request): self
    {
        return self::from([
            'titulo' => $request->input('titulo'),
            'descripcion' => $request->input('descripcion'),
            'estado' => $request->input('estado', 'pendiente'),
            'prioridad' => $request->input('prioridad', 2),
            'fecha_vencimiento' => $request->input('fecha_vencimiento'),
            'usuario_id' => $request->user()->id,
            'categoria_id' => $request->input('categoria_id'),
        ]);
    }

    /**
     * Reglas de validación personalizadas.
     */
    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'titulo' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string', 'max:65535'],
            'estado' => ['required', 'in:pendiente,completada'],
            'prioridad' => ['required', 'integer', 'min:1', 'max:3'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:today'],
            'usuario_id' => ['required', 'integer', 'exists:usuarios,id'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
        ];
    }

    /**
     * Mensajes de validación personalizados.
     */
    public static function messages(...$args): array
    {
        return [
            'titulo.required' => 'El título de la tarea es obligatorio.',
            'titulo.max' => 'El título no puede exceder 200 caracteres.',
            'estado.in' => 'El estado debe ser pendiente o completada.',
            'prioridad.min' => 'La prioridad debe ser entre 1 (alta) y 3 (baja).',
            'prioridad.max' => 'La prioridad debe ser entre 1 (alta) y 3 (baja).',
            'fecha_vencimiento.after_or_equal' => 'La fecha de vencimiento no puede ser anterior a hoy.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
        ];
    }
}
