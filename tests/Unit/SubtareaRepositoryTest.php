<?php

namespace Tests\Unit;

use App\Models\Subtarea;
use App\Models\Tarea;
use App\Repositories\SubtareaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtareaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SubtareaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new SubtareaRepository();
    }

    public function test_obtener_subtareas_por_tarea(): void
    {
        $tarea = Tarea::factory()->create();

        // Crear 3 subtareas para esta tarea
        $subtareas = Subtarea::factory()->count(3)->create(['tarea_id' => $tarea->id]);

        // Crear 2 subtareas para otra tarea (no deberían aparecer)
        $otraTarea = Tarea::factory()->create();
        Subtarea::factory()->count(2)->create(['tarea_id' => $otraTarea->id]);

        $resultado = $this->repository->obtenerPorTarea($tarea->id);

        $this->assertCount(3, $resultado);
        $this->assertTrue($resultado->every(fn($s) => $s->tarea_id === $tarea->id));
    }

    public function test_crear_subtarea(): void
    {
        $tarea = Tarea::factory()->create();

        $data = [
            'texto' => 'Nueva subtarea desde repository',
            'tarea_id' => $tarea->id,
            'estado' => 'pendiente',
        ];

        $subtarea = $this->repository->crear($data);

        $this->assertInstanceOf(Subtarea::class, $subtarea);
        $this->assertEquals('Nueva subtarea desde repository', $subtarea->texto);
        $this->assertEquals($tarea->id, $subtarea->tarea_id);
        $this->assertDatabaseHas('subtareas', $data);
    }

    public function test_actualizar_subtarea(): void
    {
        $subtarea = Subtarea::factory()->create(['texto' => 'Original']);

        $data = [
            'texto' => 'Actualizado desde repository',
            'estado' => 'completada',
        ];

        $subtareaActualizada = $this->repository->actualizar($subtarea, $data);

        $this->assertEquals('Actualizado desde repository', $subtareaActualizada->texto);
        $this->assertEquals('completada', $subtareaActualizada->estado);
    }

    public function test_eliminar_subtarea(): void
    {
        $subtarea = Subtarea::factory()->create();
        $subtareaId = $subtarea->id;

        $resultado = $this->repository->eliminar($subtarea);

        $this->assertTrue($resultado);
        $this->assertSoftDeleted('subtareas', ['id' => $subtareaId]);
    }

    public function test_toggle_estado_subtarea(): void
    {
        $subtarea = Subtarea::factory()->pendiente()->create();

        $this->assertEquals('pendiente', $subtarea->estado);

        $subtareaActualizada = $this->repository->toggleEstado($subtarea);

        $this->assertEquals('completada', $subtareaActualizada->estado);
    }

    public function test_contar_subtareas_por_tarea(): void
    {
        $tarea = Tarea::factory()->create();

        Subtarea::factory()->count(5)->create(['tarea_id' => $tarea->id]);

        $count = $this->repository->contarPorTarea($tarea->id);

        $this->assertEquals(5, $count);
    }

    public function test_contar_no_incluye_eliminadas(): void
    {
        $tarea = Tarea::factory()->create();

        Subtarea::factory()->count(5)->create(['tarea_id' => $tarea->id]);

        // Eliminar 2
        Subtarea::where('tarea_id', $tarea->id)->take(2)->get()->each->delete();

        $count = $this->repository->contarPorTarea($tarea->id);

        $this->assertEquals(3, $count); // Solo 3 no eliminadas
    }

    public function test_validar_limite_retorna_true_si_puede_agregar(): void
    {
        $tarea = Tarea::factory()->create();

        Subtarea::factory()->count(20)->create(['tarea_id' => $tarea->id]);

        $puedeAgregar = $this->repository->validarLimite($tarea->id);

        $this->assertTrue($puedeAgregar);
    }

    public function test_validar_limite_retorna_false_si_llego_a_30(): void
    {
        $tarea = Tarea::factory()->create();

        for ($i = 0; $i < 30; $i++) {
            Subtarea::create([
                'texto' => "Subtarea {$i}",
                'tarea_id' => $tarea->id,
                'estado' => 'pendiente',
            ]);
        }

        $puedeAgregar = $this->repository->validarLimite($tarea->id);

        $this->assertFalse($puedeAgregar);
    }

    public function test_contar_completadas(): void
    {
        $tarea = Tarea::factory()->create();

        Subtarea::factory()->completada()->count(3)->create(['tarea_id' => $tarea->id]);
        Subtarea::factory()->pendiente()->count(2)->create(['tarea_id' => $tarea->id]);

        $count = $this->repository->contarCompletadas($tarea->id);

        $this->assertEquals(3, $count);
    }

    public function test_contar_pendientes(): void
    {
        $tarea = Tarea::factory()->create();

        Subtarea::factory()->completada()->count(3)->create(['tarea_id' => $tarea->id]);
        Subtarea::factory()->pendiente()->count(2)->create(['tarea_id' => $tarea->id]);

        $count = $this->repository->contarPendientes($tarea->id);

        $this->assertEquals(2, $count);
    }
}
