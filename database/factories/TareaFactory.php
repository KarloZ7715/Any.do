<?php

namespace Database\Factories;

use App\Models\Subtarea;
use App\Models\Tarea;
use App\Models\Usuario;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tarea>
 */
class TareaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Tarea::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->optional(0.7)->paragraph(),
            'estado' => 'pendiente',
            'prioridad' => fake()->numberBetween(1, 3),
            'fecha_completada' => null,
            'fecha_vencimiento' => fake()->optional(0.6)->dateTimeBetween('now', '+30 days'),
            'usuario_id' => Usuario::factory(),
            'categoria_id' => function (array $attributes) {
                return Categoria::factory()->create([
                    'usuario_id' => $attributes['usuario_id'],
                ])->id;
            },
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pendiente(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'pendiente',
            'fecha_completada' => null,
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completada(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'completada',
            'fecha_completada' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the task has high priority.
     */
    public function prioridadAlta(): static
    {
        return $this->state(fn(array $attributes) => [
            'prioridad' => 1,
        ]);
    }

    /**
     * Indicate that the task has medium priority.
     */
    public function prioridadMedia(): static
    {
        return $this->state(fn(array $attributes) => [
            'prioridad' => 2,
        ]);
    }

    /**
     * Indicate that the task has low priority.
     */
    public function prioridadBaja(): static
    {
        return $this->state(fn(array $attributes) => [
            'prioridad' => 3,
        ]);
    }

    /**
     * Indicate that the task is overdue.
     */
    public function vencida(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'pendiente',
            'fecha_vencimiento' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'fecha_completada' => null,
        ]);
    }

    /**
     * Indicate that the task has no category.
     */
    public function sinCategoria(): static
    {
        return $this->state(fn(array $attributes) => [
            'categoria_id' => null,
        ]);
    }

    /**
     * Indicate that the task has no due date.
     */
    public function sinFechaVencimiento(): static
    {
        return $this->state(fn(array $attributes) => [
            'fecha_vencimiento' => null,
        ]);
    }

    /**
     * Indicate that the task has no description.
     */
    public function sinDescripcion(): static
    {
        return $this->state(fn(array $attributes) => [
            'descripcion' => null,
        ]);
    }

    /**
     * Create a task for a specific user.
     */
    public function forUser(Usuario $usuario): static
    {
        return $this->state(fn(array $attributes) => [
            'usuario_id' => $usuario->id,
        ]);
    }

    /**
     * Create a task for a specific category.
     */
    public function forCategory(Categoria $categoria): static
    {
        return $this->state(fn(array $attributes) => [
            'categoria_id' => $categoria->id,
        ]);
    }

    /**
     * Generar subtareas automáticamente (0-5).
     */
    public function conSubtareas(): static
    {
        return $this->afterCreating(function (Tarea $tarea) {
            Subtarea::factory()
                ->count(fake()->numberBetween(0, 5))
                ->create(['tarea_id' => $tarea->id]);
        });
    }
}
