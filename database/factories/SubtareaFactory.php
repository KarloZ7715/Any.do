<?php

namespace Database\Factories;

use App\Models\Subtarea;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subtarea>
 */
class SubtareaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Subtarea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'texto' => fake()->sentence(),
            'tarea_id' => Tarea::factory(),
            'estado' => fake()->randomElement(['pendiente', 'completada']),
        ];
    }

    /**
     * Crear una subtarea pendiente.
     */
    public function pendiente(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Crear una subtarea completada.
     */
    public function completada(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'completada',
        ]);
    }

    /**
     * Crear una subtarea corta.
     */
    public function corta(): static
    {
        return $this->state(fn(array $attributes) => [
            'texto' => fake()->words(3, true),
        ]);
    }

    /**
     * Crear una subtarea larga.
     */
    public function larga(): static
    {
        return $this->state(fn(array $attributes) => [
            'texto' => fake()->sentence(15),
        ]);
    }

    /**
     * Crear una subtarea para una tarea específica.
     */
    public function paraTarea(Tarea $tarea): static
    {
        return $this->state(fn(array $attributes) => [
            'tarea_id' => $tarea->id,
        ]);
    }
}
