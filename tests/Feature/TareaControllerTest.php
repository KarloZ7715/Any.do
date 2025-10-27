<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Tarea;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de integración para TareaController.
 * 
 * Valida: autorización, CRUD completo, filtros, respuestas HTTP.
 */
class TareaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $usuario;
    private Usuario $otroUsuario;
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = Usuario::factory()->create(['rol' => 'usuario']);
        $this->otroUsuario = Usuario::factory()->create(['rol' => 'usuario']);
        $this->admin = Usuario::factory()->create(['rol' => 'admin']);
    }

    public function test_usuario_puede_ver_sus_tareas(): void
    {
        $tareaPropia = Tarea::factory()->create(['usuario_id' => $this->usuario->id]);
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);

        $response = $this->actingAs($this->usuario)->get(route('tareas.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Tareas/Index')
                ->has('tareas.data', 1)
        );
    }

    public function test_admin_puede_ver_todas_las_tareas(): void
    {
        Tarea::factory()->count(3)->create(['usuario_id' => $this->usuario->id]);
        Tarea::factory()->count(2)->create(['usuario_id' => $this->otroUsuario->id]);

        $response = $this->actingAs($this->admin)->get(route('tareas.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Tareas/Index')
                ->has('tareas.data', 5)
        );
    }

    public function test_usuario_puede_crear_tarea(): void
    {
        $categoria = Categoria::factory()->create();

        $datosTarea = [
            'titulo' => 'Nueva tarea de prueba',
            'descripcion' => 'Descripción de la tarea',
            'estado' => 'pendiente',
            'prioridad' => 1,
            'fecha_vencimiento' => now()->addDays(5)->format('Y-m-d'),
            'categoria_id' => $categoria->id,
        ];

        $response = $this->actingAs($this->usuario)->post(route('tareas.store'), $datosTarea);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Tarea creada exitosamente');

        $this->assertDatabaseHas('tareas', [
            'titulo' => 'Nueva tarea de prueba',
            'usuario_id' => $this->usuario->id,
            'categoria_id' => $categoria->id,
        ]);
    }

    public function test_usuario_puede_actualizar_su_tarea(): void
    {
        $tarea = Tarea::factory()->create(['usuario_id' => $this->usuario->id]);

        $datosActualizados = [
            'titulo' => 'Título actualizado',
            'descripcion' => $tarea->descripcion,
            'estado' => $tarea->estado,
            'prioridad' => 2,
            'fecha_vencimiento' => $tarea->fecha_vencimiento?->format('Y-m-d'),
            'categoria_id' => $tarea->categoria_id,
        ];

        $response = $this->actingAs($this->usuario)
            ->put(route('tareas.update', $tarea), $datosActualizados);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Tarea actualizada exitosamente');

        $this->assertDatabaseHas('tareas', [
            'id' => $tarea->id,
            'titulo' => 'Título actualizado',
            'prioridad' => 2,
        ]);
    }

    public function test_usuario_no_puede_actualizar_tarea_ajena(): void
    {
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);

        $datosActualizados = [
            'titulo' => 'Intento de actualización',
            'descripcion' => $tareaAjena->descripcion,
            'estado' => $tareaAjena->estado,
            'prioridad' => $tareaAjena->prioridad,
            'fecha_vencimiento' => $tareaAjena->fecha_vencimiento?->format('Y-m-d'),
            'categoria_id' => $tareaAjena->categoria_id,
        ];

        $response = $this->actingAs($this->usuario)
            ->put(route('tareas.update', $tareaAjena), $datosActualizados);

        $response->assertForbidden();
    }

    public function test_admin_puede_actualizar_cualquier_tarea(): void
    {
        $tareaDeOtroUsuario = Tarea::factory()->create(['usuario_id' => $this->usuario->id]);

        $datosActualizados = [
            'titulo' => 'Actualizado por admin',
            'descripcion' => $tareaDeOtroUsuario->descripcion,
            'estado' => $tareaDeOtroUsuario->estado,
            'prioridad' => 3,
            'fecha_vencimiento' => $tareaDeOtroUsuario->fecha_vencimiento?->format('Y-m-d'),
            'categoria_id' => $tareaDeOtroUsuario->categoria_id,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('tareas.update', $tareaDeOtroUsuario), $datosActualizados);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Tarea actualizada exitosamente');

        $this->assertDatabaseHas('tareas', [
            'id' => $tareaDeOtroUsuario->id,
            'titulo' => 'Actualizado por admin',
        ]);
    }

    public function test_usuario_puede_eliminar_su_tarea(): void
    {
        $tarea = Tarea::factory()->create(['usuario_id' => $this->usuario->id]);

        $response = $this->actingAs($this->usuario)
            ->delete(route('tareas.destroy', $tarea));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Tarea eliminada exitosamente');

        $this->assertSoftDeleted('tareas', ['id' => $tarea->id]);
    }

    public function test_usuario_no_puede_eliminar_tarea_ajena(): void
    {
        $tareaAjena = Tarea::factory()->create(['usuario_id' => $this->otroUsuario->id]);

        $response = $this->actingAs($this->usuario)
            ->delete(route('tareas.destroy', $tareaAjena));

        $response->assertForbidden();

        $this->assertDatabaseHas('tareas', [
            'id' => $tareaAjena->id,
            'deleted_at' => null,
        ]);
    }

    public function test_usuario_puede_toggle_estado(): void
    {
        $tarea = Tarea::factory()->pendiente()->create(['usuario_id' => $this->usuario->id]);

        $this->assertEquals('pendiente', $tarea->estado);

        $response = $this->actingAs($this->usuario)
            ->patch(route('tareas.toggle', $tarea));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Tarea marcada como completada');

        $tarea->refresh();
        $this->assertEquals('completada', $tarea->estado);
        $this->assertNotNull($tarea->fecha_completada);
    }

    public function test_filtros_funcionan_correctamente(): void
    {
        $categoria = Categoria::factory()->create();

        // Crear tareas con diferentes características
        Tarea::factory()->pendiente()->create([
            'usuario_id' => $this->usuario->id,
            'prioridad' => 1,
            'categoria_id' => $categoria->id,
        ]);

        Tarea::factory()->completada()->create([
            'usuario_id' => $this->usuario->id,
            'prioridad' => 2,
            'categoria_id' => $categoria->id,
        ]);

        Tarea::factory()->pendiente()->create([
            'usuario_id' => $this->usuario->id,
            'prioridad' => 3,
        ]);

        // Filtro por estado
        $response = $this->actingAs($this->usuario)
            ->get(route('tareas.index', ['estado' => 'pendiente']));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->has('tareas.data', 2)
        );

        // Filtro por prioridad
        $response = $this->actingAs($this->usuario)
            ->get(route('tareas.index', ['prioridad' => 1]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->has('tareas.data', 1)
        );

        // Filtro por categoría
        $response = $this->actingAs($this->usuario)
            ->get(route('tareas.index', ['categoria_id' => $categoria->id]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->has('tareas.data', 2)
        );
    }

    public function test_validacion_requiere_titulo(): void
    {
        $datosSinTitulo = [
            'descripcion' => 'Sin título',
            'estado' => 'pendiente',
            'prioridad' => 1,
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('tareas.store'), $datosSinTitulo);

        $response->assertSessionHasErrors('titulo');
    }

    public function test_validacion_fecha_vencimiento_no_puede_ser_pasada(): void
    {
        $datosConFechaPasada = [
            'titulo' => 'Tarea con fecha inválida',
            'descripcion' => 'Test',
            'estado' => 'pendiente',
            'prioridad' => 1,
            'fecha_vencimiento' => now()->subDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->usuario)
            ->post(route('tareas.store'), $datosConFechaPasada);

        $response->assertSessionHasErrors('fecha_vencimiento');
    }

    public function test_usuario_no_autenticado_no_puede_acceder_a_tareas(): void
    {
        $response = $this->get(route('tareas.index'));

        $response->assertRedirect(route('login'));
    }
}
