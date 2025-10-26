<?php

namespace Database\Factories;

use App\Models\Comentario;
use App\Models\Usuario;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comentario>
 */
class ComentarioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Comentario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'texto' => fake()->paragraph(),
            'usuario_id' => Usuario::factory(),
            'tarea_id' => Tarea::factory(),
        ];
    }

    /**
     * Create a short comment.
     */
    public function corto(): static
    {
        return $this->state(fn(array $attributes) => [
            'texto' => fake()->sentence(),
        ]);
    }

    /**
     * Create a long comment.
     */
    public function largo(): static
    {
        return $this->state(fn(array $attributes) => [
            'texto' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * Create a comment for a specific user.
     */
    public function forUser(Usuario $usuario): static
    {
        return $this->state(fn(array $attributes) => [
            'usuario_id' => $usuario->id,
        ]);
    }

    /**
     * Create a comment for a specific task.
     */
    public function forTask(Tarea $tarea): static
    {
        return $this->state(fn(array $attributes) => [
            'tarea_id' => $tarea->id,
        ]);
    }
}
