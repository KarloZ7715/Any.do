<?php

namespace Tests\Feature;

use App\Models\Subtarea;
use App\Models\Tarea;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de integración para SubtareaController.
 * 
 * Valida: autorización, CRUD completo, toggle estado, límite 30, respuestas HTTP.
 */
class SubtareaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;
    private Usuario $otroUsuario;
    private Usuario $admin;
    private Tarea $tarea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create(['rol' => 'usuario']);
        $this->otroUsuario = Usuario::factory()->create(['rol' => 'usuario']);
        $this->admin = Usuario::factory()->create(['rol' => 'admin']);

        // Tarea del usuario principal para usar en tests
        $this->tarea = Tarea::factory()->create(['usuario_id' => $this->usuario->id]);
    }

    public function test_usuario_puede_crear_subtarea_en_su_tarea(): void
    {
        $datosSubtarea = [
            'texto' => 'Nueva subtarea de prueba',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.store', $this->tarea), $datosSubtarea);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Subtarea creada exitosamente');

        $this->assertDatabaseHas('subtareas', [
            'texto' => 'Nueva subtarea de prueba',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ]);
    }

    public function test_usuario_no_puede_crear_subtarea_en_tarea_ajena(): void
    {
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);

        $datosSubtarea = [
            'texto' => 'Intento de crear subtarea',
            'estado' => 'pendiente',
            'tarea_id' => $tareaAjena->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.store', $tareaAjena), $datosSubtarea);

        $response->assertForbidden();

        $this->assertDatabaseMissing('subtareas', [
            'texto' => 'Intento de crear subtarea',
            'tarea_id' => $tareaAjena->id,
        ]);
    }

    public function test_admin_puede_crear_subtarea_en_cualquier_tarea(): void
    {
        $tareaOtroUsuario = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);

        $datosSubtarea = [
            'texto' => 'Subtarea creada por admin',
            'estado' => 'pendiente',
            'tarea_id' => $tareaOtroUsuario->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('subtareas.store', $tareaOtroUsuario), $datosSubtarea);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Subtarea creada exitosamente');

        $this->assertDatabaseHas('subtareas', [
            'texto' => 'Subtarea creada por admin',
            'tarea_id' => $tareaOtroUsuario->id,
        ]);
    }

    public function test_no_puede_crear_mas_de_30_subtareas(): void
    {
        // Crear 30 subtareas directamente sin factory
        for ($i = 1; $i <= 30; $i++) {
            Subtarea::create([
                'texto' => "Subtarea $i",
                'estado' => 'pendiente',
                'tarea_id' => $this->tarea->id,
            ]);
        }

        // Intentar crear la 31
        $datosSubtarea = [
            'texto' => 'Subtarea 31',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.store', $this->tarea), $datosSubtarea);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'No se pueden agregar más de 30 subtareas por tarea');

        $this->assertDatabaseMissing('subtareas', [
            'texto' => 'Subtarea 31',
            'tarea_id' => $this->tarea->id,
        ]);
    }

    public function test_usuario_puede_actualizar_su_subtarea(): void
    {
        $subtarea = Subtarea::create([
            'texto' => 'Subtarea original',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ]);

        $datosActualizados = [
            'texto' => 'Subtarea actualizada',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->patch(route('subtareas.update', [$this->tarea, $subtarea]), $datosActualizados);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Subtarea actualizada');

        $this->assertDatabaseHas('subtareas', [
            'id' => $subtarea->id,
            'texto' => 'Subtarea actualizada',
        ]);
    }

    public function test_usuario_no_puede_actualizar_subtarea_ajena(): void
    {
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);
        $subtareaAjena = Subtarea::create([
            'texto' => 'Subtarea ajena',
            'estado' => 'pendiente',
            'tarea_id' => $tareaAjena->id,
        ]);

        $datosActualizados = [
            'texto' => 'Intento de actualización',
            'estado' => 'pendiente',
            'tarea_id' => $tareaAjena->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->patch(route('subtareas.update', [$tareaAjena, $subtareaAjena]), $datosActualizados);

        $response->assertForbidden();

        $this->assertDatabaseHas('subtareas', [
            'id' => $subtareaAjena->id,
            'texto' => 'Subtarea ajena',
        ]);
    }

    public function test_admin_puede_actualizar_cualquier_subtarea(): void
    {
        $tareaOtroUsuario = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);
        $subtarea = Subtarea::create([
            'texto' => 'Subtarea original',
            'estado' => 'pendiente',
            'tarea_id' => $tareaOtroUsuario->id,
        ]);

        $datosActualizados = [
            'texto' => 'Actualizada por admin',
            'estado' => 'completada',
            'tarea_id' => $tareaOtroUsuario->id,
        ];

        $response = $this->actingAs($this->admin)
            ->patch(route('subtareas.update', [$tareaOtroUsuario, $subtarea]), $datosActualizados);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Subtarea actualizada');

        $this->assertDatabaseHas('subtareas', [
            'id' => $subtarea->id,
            'texto' => 'Actualizada por admin',
            'estado' => 'completada',
        ]);
    }

    public function test_usuario_puede_eliminar_su_subtarea(): void
    {
        $subtarea = Subtarea::create([
            'texto' => 'Subtarea para eliminar',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ]);

        $response = $this->actingAs($this->usuario)
            ->delete(route('subtareas.destroy', [$this->tarea, $subtarea]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Subtarea eliminada');

        $this->assertSoftDeleted('subtareas', ['id' => $subtarea->id]);
    }

    public function test_usuario_no_puede_eliminar_subtarea_ajena(): void
    {
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);
        $subtareaAjena = Subtarea::create([
            'texto' => 'Subtarea ajena',
            'estado' => 'pendiente',
            'tarea_id' => $tareaAjena->id,
        ]);

        $response = $this->actingAs($this->usuario)
            ->delete(route('subtareas.destroy', [$tareaAjena, $subtareaAjena]));

        $response->assertForbidden();

        $this->assertDatabaseHas('subtareas', [
            'id' => $subtareaAjena->id,
            'deleted_at' => null,
        ]);
    }

    public function test_usuario_puede_toggle_estado_de_su_subtarea(): void
    {
        $subtarea = Subtarea::create([
            'texto' => 'Subtarea para toggle',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ]);

        $this->assertEquals('pendiente', $subtarea->estado);

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.toggle', [$this->tarea, $subtarea]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Estado actualizado');

        $subtarea->refresh();
        $this->assertEquals('completada', $subtarea->estado);
    }

    public function test_toggle_cambia_de_completada_a_pendiente(): void
    {
        $subtarea = Subtarea::create([
            'texto' => 'Subtarea completada',
            'estado' => 'completada',
            'tarea_id' => $this->tarea->id,
        ]);

        $this->assertEquals('completada', $subtarea->estado);

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.toggle', [$this->tarea, $subtarea]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Estado actualizado');

        $subtarea->refresh();
        $this->assertEquals('pendiente', $subtarea->estado);
    }

    public function test_usuario_no_puede_toggle_subtarea_ajena(): void
    {
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);
        $subtareaAjena = Subtarea::create([
            'texto' => 'Subtarea ajena',
            'estado' => 'pendiente',
            'tarea_id' => $tareaAjena->id,
        ]);

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.toggle', [$tareaAjena, $subtareaAjena]));

        $response->assertForbidden();

        $this->assertDatabaseHas('subtareas', [
            'id' => $subtareaAjena->id,
            'estado' => 'pendiente',
        ]);
    }

    public function test_validacion_falla_si_texto_esta_vacio(): void
    {
        $datosInvalidos = [
            'texto' => '',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.store', $this->tarea), $datosInvalidos);

        $response->assertRedirect();
        $response->assertSessionHasErrors('texto');

        $this->assertDatabaseMissing('subtareas', [
            'tarea_id' => $this->tarea->id,
            'texto' => '',
        ]);
    }

    public function test_puede_crear_subtarea_despues_de_eliminar_una(): void
    {
        // Crear 30 subtareas
        for ($i = 1; $i <= 30; $i++) {
            Subtarea::create([
                'texto' => "Subtarea $i",
                'estado' => 'pendiente',
                'tarea_id' => $this->tarea->id,
            ]);
        }

        // Eliminar una (soft delete)
        $subtarea = $this->tarea->subtareas()->first();
        $subtarea->delete();

        // Debe poder crear otra (las eliminadas no cuentan)
        $datosNuevaSubtarea = [
            'texto' => 'Nueva subtarea después de eliminar',
            'estado' => 'pendiente',
            'tarea_id' => $this->tarea->id,
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('subtareas.store', $this->tarea), $datosNuevaSubtarea);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Subtarea creada exitosamente');

        $this->assertDatabaseHas('subtareas', [
            'texto' => 'Nueva subtarea después de eliminar',
            'tarea_id' => $this->tarea->id,
        ]);
    }
}
