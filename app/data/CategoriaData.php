<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Regex;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CategoriaData extends Data
{
    public function __construct(
        #[Required, Max(100)]
        public string $nombre,

        #[Max(500)]
        public ?string $descripcion,

        #[Required, Regex('/^#[0-9A-Fa-f]{6}$/')]
        public string $color,

        #[Max(50)]
        public ?string $icono,
    ) {
    }

    /**
     * Mensajes de validación personalizados en español.
     */
    public static function messages(...$args): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres',
            'color.required' => 'El color es obligatorio',
            'color.regex' => 'El color debe ser un código hexadecimal válido (ej: #3B82F6)',
            'icono.max' => 'El nombre del icono no puede tener más de 50 caracteres',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres',
        ];
    }
}
