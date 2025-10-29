<?php

namespace Tests\Unit;

use App\Data\SubtareaData;
use App\Models\Subtarea;
use App\Models\Tarea;
use App\Repositories\SubtareaRepository;
use App\Services\SubtareaService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtareaServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubtareaService $service;
    private SubtareaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new SubtareaRepository();
        $this->service = new SubtareaService($this->repository);
    }

    public function test_crear_subtarea_exitosamente(): void
    {
        $tarea = Tarea::factory()->create();

        $data = SubtareaData::from([
            'texto' => 'Nueva subtarea de prueba',
            'tarea_id' => $tarea->id,
            'estado' => 'pendiente',
        ]);

        $subtarea = $this->service->crearSubtarea($data);

        $this->assertInstanceOf(Subtarea::class, $subtarea);
        $this->assertEquals('Nueva subtarea de prueba', $subtarea->texto);
        $this->assertEquals($tarea->id, $subtarea->tarea_id);
        $this->assertEquals('pendiente', $subtarea->estado);
    }

    public function test_crear_subtarea_con_estado_por_defecto(): void
    {
        $tarea = Tarea::factory()->create();

        $data = SubtareaData::from([
            'texto' => 'Subtarea sin estado',
            'tarea_id' => $tarea->id,
        ]);

        $subtarea = $this->service->crearSubtarea($data);

        $this->assertEquals('pendiente', $subtarea->estado);
    }

    public function test_no_puede_crear_mas_de_30_subtareas(): void
    {
        $tarea = Tarea::factory()->create();

        // Crear 30 subtareas
        for ($i = 0; $i < 30; $i++) {
            Subtarea::create([
                'texto' => "Subtarea {$i}",
                'tarea_id' => $tarea->id,
                'estado' => 'pendiente',
            ]);
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No se pueden agregar más de 30 subtareas por tarea');

        $data = SubtareaData::from([
            'texto' => 'Subtarea 31',
            'tarea_id' => $tarea->id,
        ]);

        $this->service->crearSubtarea($data);
    }
    public function test_actualizar_subtarea_exitosamente(): void
    {
        $subtarea = Subtarea::factory()->create(['texto' => 'Texto original']);

        $data = SubtareaData::from([
            'texto' => 'Texto actualizado',
            'tarea_id' => $subtarea->tarea_id,
            'estado' => 'completada',
        ]);

        $subtareaActualizada = $this->service->actualizarSubtarea($subtarea, $data);

        $this->assertEquals('Texto actualizado', $subtareaActualizada->texto);
        $this->assertEquals('completada', $subtareaActualizada->estado);
    }

    public function test_actualizar_solo_texto_de_subtarea(): void
    {
        $subtarea = Subtarea::factory()->pendiente()->create(['texto' => 'Original']);

        $data = SubtareaData::from([
            'texto' => 'Solo texto nuevo',
            'tarea_id' => $subtarea->tarea_id,
        ]);

        $subtareaActualizada = $this->service->actualizarSubtarea($subtarea, $data);

        $this->assertEquals('Solo texto nuevo', $subtareaActualizada->texto);
        $this->assertEquals('pendiente', $subtareaActualizada->estado); // No cambió
    }

    public function test_eliminar_subtarea_exitosamente(): void
    {
        $subtarea = Subtarea::factory()->create();
        $subtareaId = $subtarea->id;

        $resultado = $this->service->eliminarSubtarea($subtarea);

        $this->assertTrue($resultado);
        $this->assertSoftDeleted('subtareas', ['id' => $subtareaId]);
    }

    public function test_cambiar_estado_de_pendiente_a_completada(): void
    {
        $subtarea = Subtarea::factory()->pendiente()->create();

        $this->assertEquals('pendiente', $subtarea->estado);

        $subtareaActualizada = $this->service->cambiarEstado($subtarea);

        $this->assertEquals('completada', $subtareaActualizada->estado);
    }

    public function test_cambiar_estado_de_completada_a_pendiente(): void
    {
        $subtarea = Subtarea::factory()->completada()->create();

        $this->assertEquals('completada', $subtarea->estado);

        $subtareaActualizada = $this->service->cambiarEstado($subtarea);

        $this->assertEquals('pendiente', $subtareaActualizada->estado);
    }

    public function test_puede_crear_subtarea_si_hay_menos_de_30(): void
    {
        $tarea = Tarea::factory()->create();

        // Crear 29 subtareas usando create directo (bypass factory definition de tarea_id)
        for ($i = 0; $i < 29; $i++) {
            Subtarea::create([
                'texto' => "Subtarea {$i}",
                'tarea_id' => $tarea->id,
                'estado' => 'pendiente',
            ]);
        }

        // Verificar count antes de crear
        $countAntes = Subtarea::where('tarea_id', $tarea->id)->count();
        $this->assertEquals(29, $countAntes);

        $data = SubtareaData::from([
            'texto' => 'Subtarea 30',
            'tarea_id' => $tarea->id,
        ]);

        $subtarea = $this->service->crearSubtarea($data);

        $this->assertInstanceOf(Subtarea::class, $subtarea);
        $this->assertEquals(30, Subtarea::where('tarea_id', $tarea->id)->count());
    }
    public function test_limite_30_subtareas_no_cuenta_eliminadas(): void
    {
        $tarea = Tarea::factory()->create();

        // Crear 30 subtareas y eliminar 1
        for ($i = 0; $i < 30; $i++) {
            Subtarea::create([
                'texto' => "Subtarea {$i}",
                'tarea_id' => $tarea->id,
                'estado' => 'pendiente',
            ]);
        }

        $subtareaAEliminar = Subtarea::where('tarea_id', $tarea->id)->first();
        $subtareaAEliminar->delete();

        // Ahora debería poder crear otra (solo hay 29 no eliminadas)
        $data = SubtareaData::from([
            'texto' => 'Nueva después de eliminar',
            'tarea_id' => $tarea->id,
        ]);

        $subtarea = $this->service->crearSubtarea($data);

        $this->assertInstanceOf(Subtarea::class, $subtarea);
        $this->assertEquals(30, Subtarea::where('tarea_id', $tarea->id)->count()); // 30 sin soft-deleted
    }
}