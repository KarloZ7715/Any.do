<?php

namespace Tests\Unit;

use App\Models\Subtarea;
use App\Models\Tarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtareaTest extends TestCase
{
    use RefreshDatabase;

    public function test_subtarea_pertenece_a_tarea(): void
    {
        $tarea = Tarea::factory()->create();
        $subtarea = Subtarea::factory()->create(['tarea_id' => $tarea->id]);

        $this->assertInstanceOf(Tarea::class, $subtarea->tarea);
        $this->assertEquals($tarea->id, $subtarea->tarea->id);
    }

    public function test_subtarea_tiene_fillable_correcto(): void
    {
        $fillable = (new Subtarea())->getFillable();

        $this->assertContains('texto', $fillable);
        $this->assertContains('tarea_id', $fillable);
        $this->assertContains('estado', $fillable);
    }

    public function test_subtarea_castea_estado_correctamente(): void
    {
        $subtarea = Subtarea::factory()->create(['estado' => 'pendiente']);

        $this->assertIsString($subtarea->estado);
        $this->assertEquals('pendiente', $subtarea->estado);
    }

    public function test_subtarea_esta_completada(): void
    {
        $subtareaCompletada = Subtarea::factory()->completada()->create();
        $subtareaPendiente = Subtarea::factory()->pendiente()->create();

        $this->assertTrue($subtareaCompletada->estaCompletada());
        $this->assertFalse($subtareaPendiente->estaCompletada());
    }

    public function test_subtarea_esta_pendiente(): void
    {
        $subtareaCompletada = Subtarea::factory()->completada()->create();
        $subtareaPendiente = Subtarea::factory()->pendiente()->create();

        $this->assertFalse($subtareaCompletada->estaPendiente());
        $this->assertTrue($subtareaPendiente->estaPendiente());
    }

    public function test_toggle_estado_cambia_de_pendiente_a_completada(): void
    {
        $subtarea = Subtarea::factory()->pendiente()->create();

        $this->assertEquals('pendiente', $subtarea->estado);

        $subtarea->toggleEstado();

        $this->assertEquals('completada', $subtarea->fresh()->estado);
    }

    public function test_toggle_estado_cambia_de_completada_a_pendiente(): void
    {
        $subtarea = Subtarea::factory()->completada()->create();

        $this->assertEquals('completada', $subtarea->estado);

        $subtarea->toggleEstado();

        $this->assertEquals('pendiente', $subtarea->fresh()->estado);
    }

    public function test_completar_cambia_estado_a_completada(): void
    {
        $subtarea = Subtarea::factory()->pendiente()->create();

        $subtarea->completar();

        $this->assertEquals('completada', $subtarea->fresh()->estado);
    }

    public function test_marcar_pendiente_cambia_estado_a_pendiente(): void
    {
        $subtarea = Subtarea::factory()->completada()->create();

        $subtarea->marcarPendiente();

        $this->assertEquals('pendiente', $subtarea->fresh()->estado);
    }

    public function test_soft_deletes_funciona(): void
    {
        $subtarea = Subtarea::factory()->create();
        $subtareaId = $subtarea->id;

        $subtarea->delete();

        $this->assertSoftDeleted('subtareas', ['id' => $subtareaId]);
        $this->assertNotNull($subtarea->fresh()->deleted_at);
    }

    public function test_factory_crea_subtarea_con_datos_validos(): void
    {
        $subtarea = Subtarea::factory()->create();

        $this->assertNotNull($subtarea->texto);
        $this->assertNotNull($subtarea->tarea_id);
        $this->assertNotNull($subtarea->estado);
        $this->assertContains($subtarea->estado, ['pendiente', 'completada']);
    }

    public function test_factory_state_pendiente_crea_subtarea_pendiente(): void
    {
        $subtarea = Subtarea::factory()->pendiente()->create();

        $this->assertEquals('pendiente', $subtarea->estado);
    }

    public function test_factory_state_completada_crea_subtarea_completada(): void
    {
        $subtarea = Subtarea::factory()->completada()->create();

        $this->assertEquals('completada', $subtarea->estado);
    }
}
