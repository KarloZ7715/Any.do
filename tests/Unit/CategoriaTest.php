<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Tarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_categoria_tiene_muchas_tareas(): void
    {
        $categoria = Categoria::factory()->create();
        Tarea::factory()->count(5)->create(['categoria_id' => $categoria->id]);

        $this->assertCount(5, $categoria->tareas);
        $this->assertInstanceOf(Tarea::class, $categoria->tareas->first());
    }

    public function test_total_tareas_cuenta_correctamente(): void
    {
        $categoria = Categoria::factory()->create();
        Tarea::factory()->count(3)->create(['categoria_id' => $categoria->id]);

        $this->assertEquals(3, $categoria->totalTareas());
    }

    public function test_total_tareas_pendientes_cuenta_correctamente(): void
    {
        $categoria = Categoria::factory()->create();
        Tarea::factory()->count(2)->create([
            'categoria_id' => $categoria->id,
            'estado' => 'pendiente',
        ]);
        Tarea::factory()->count(3)->create([
            'categoria_id' => $categoria->id,
            'estado' => 'completada',
        ]);

        $this->assertEquals(2, $categoria->totalTareasPendientes());
    }

    public function test_categoria_tiene_color_por_defecto(): void
    {
        $categoria = Categoria::factory()->create();

        $this->assertNotNull($categoria->color);
        $this->assertStringStartsWith('#', $categoria->color);
    }
}
