<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categoria>
 */
class CategoriaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Categoria::class;

    /**
     * Colores predefinidos para categorías.
     */
    protected static array $colores = [
        '#ef4444', // red
        '#f97316', // orange
        '#f59e0b', // amber
        '#eab308', // yellow
        '#84cc16', // lime
        '#22c55e', // green
        '#10b981', // emerald
        '#14b8a6', // teal
        '#06b6d4', // cyan
        '#0ea5e9', // sky
        '#3b82f6', // blue
        '#6366f1', // indigo
        '#8b5cf6', // violet
        '#a855f7', // purple
        '#d946ef', // fuchsia
        '#ec4899', // pink
        '#f43f5e', // rose
    ];

    /**
     * Iconos predefinidos de Lucide.
     */
    protected static array $iconos = [
        'briefcase',
        'home',
        'shopping-cart',
        'heart',
        'star',
        'bookmark',
        'calendar',
        'clock',
        'flag',
        'target',
        'zap',
        'coffee',
        'book',
        'code',
        'music',
        'dumbbell',
        'plane',
        'gift',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->optional(0.7)->sentence(),
            'color' => fake()->randomElement(self::$colores),
            'icono' => fake()->randomElement(self::$iconos),
            'usuario_id' => \App\Models\Usuario::factory(),
        ];
    }

    /**
     * Create a category with a specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn(array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Create a category with a specific icon.
     */
    public function withIcon(string $icon): static
    {
        return $this->state(fn(array $attributes) => [
            'icono' => $icon,
        ]);
    }
}
