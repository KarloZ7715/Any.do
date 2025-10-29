<?php

namespace Database\Seeders;

use App\Models\Subtarea;
use App\Models\Tarea;
use Illuminate\Database\Seeder;

class SubtareaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las tareas existentes
        $tareas = Tarea::all();

        // Crear 2-4 subtareas para cada tarea
        $tareas->each(function (Tarea $tarea) {
            $cantidadSubtareas = fake()->numberBetween(2, 4);

            Subtarea::factory()
                ->count($cantidadSubtareas)
                ->create(['tarea_id' => $tarea->id]);
        });
    }
}
