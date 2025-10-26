<?php

namespace Tests\Unit;

use App\Models\Tarea;
use App\Models\Usuario;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TareaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tarea_pertenece_a_usuario(): void
    {
        $usuario = Usuario::factory()->create();
        $tarea = Tarea::factory()->create(['usuario_id' => $usuario->id]);

        $this->assertInstanceOf(Usuario::class, $tarea->usuario);
        $this->assertEquals($usuario->id, $tarea->usuario->id);
    }

    public function test_tarea_puede_tener_categoria(): void
    {
        $categoria = Categoria::factory()->create();
        $tarea = Tarea::factory()->create(['categoria_id' => $categoria->id]);

        $this->assertInstanceOf(Categoria::class, $tarea->categoria);
        $this->assertEquals($categoria->id, $tarea->categoria->id);
    }

    public function test_tarea_puede_no_tener_categoria(): void
    {
        $tarea = Tarea::factory()->create(['categoria_id' => null]);

        $this->assertNull($tarea->categoria);
    }

    public function test_tarea_esta_completada(): void
    {
        $tareaCompletada = Tarea::factory()->create([
            'estado' => 'completada',
            'fecha_completada' => now(),
        ]);

        $tareaPendiente = Tarea::factory()->create([
            'estado' => 'pendiente',
        ]);

        $this->assertTrue($tareaCompletada->estaCompletada());
        $this->assertFalse($tareaPendiente->estaCompletada());
    }

    public function test_tarea_esta_vencida(): void
    {
        $tareaVencida = Tarea::factory()->create([
            'fecha_vencimiento' => now()->subDay(),
            'estado' => 'pendiente',
        ]);

        $tareaNoVencida = Tarea::factory()->create([
            'fecha_vencimiento' => now()->addDay(),
            'estado' => 'pendiente',
        ]);

        $tareaCompletada = Tarea::factory()->create([
            'fecha_vencimiento' => now()->subDay(),
            'estado' => 'completada',
        ]);

        $this->assertTrue($tareaVencida->estaVencida());
        $this->assertFalse($tareaNoVencida->estaVencida());
        $this->assertFalse($tareaCompletada->estaVencida());
    }

    public function test_completar_tarea_actualiza_estado_y_fecha(): void
    {
        $tarea = Tarea::factory()->create(['estado' => 'pendiente']);

        $tarea->completar();

        $this->assertEquals('completada', $tarea->estado);
        $this->assertNotNull($tarea->fecha_completada);
        $this->assertTrue($tarea->estaCompletada());
    }

    public function test_marcar_pendiente_limpia_fecha_completada(): void
    {
        $tarea = Tarea::factory()->create([
            'estado' => 'completada',
            'fecha_completada' => now(),
        ]);

        $tarea->marcarPendiente();

        $this->assertEquals('pendiente', $tarea->estado);
        $this->assertNull($tarea->fecha_completada);
        $this->assertFalse($tarea->estaCompletada());
    }

    public function test_scope_pendientes_filtra_correctamente(): void
    {
        Tarea::factory()->create(['estado' => 'pendiente']);
        Tarea::factory()->create(['estado' => 'pendiente']);
        Tarea::factory()->create(['estado' => 'completada']);

        $tareasPendientes = Tarea::pendientes()->get();

        $this->assertCount(2, $tareasPendientes);
        $this->assertTrue($tareasPendientes->every(fn($t) => $t->estado === 'pendiente'));
    }

    public function test_scope_completadas_filtra_correctamente(): void
    {
        Tarea::factory()->create(['estado' => 'pendiente']);
        Tarea::factory()->create(['estado' => 'completada']);
        Tarea::factory()->create(['estado' => 'completada']);

        $tareasCompletadas = Tarea::completadas()->get();

        $this->assertCount(2, $tareasCompletadas);
        $this->assertTrue($tareasCompletadas->every(fn($t) => $t->estado === 'completada'));
    }

    public function test_scope_vencidas_filtra_correctamente(): void
    {
        Tarea::factory()->create([
            'fecha_vencimiento' => now()->subDay(),
            'estado' => 'pendiente',
        ]);
        Tarea::factory()->create([
            'fecha_vencimiento' => now()->addDay(),
            'estado' => 'pendiente',
        ]);

        $tareasVencidas = Tarea::vencidas()->get();

        $this->assertCount(1, $tareasVencidas);
    }
}
