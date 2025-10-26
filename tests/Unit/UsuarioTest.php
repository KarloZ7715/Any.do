<?php

namespace Tests\Unit;

use App\Models\Usuario;
use App\Models\Tarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_tiene_muchas_tareas(): void
    {
        $usuario = Usuario::factory()->create();
        Tarea::factory()->count(3)->create(['usuario_id' => $usuario->id]);

        $this->assertCount(3, $usuario->tareas);
        $this->assertInstanceOf(Tarea::class, $usuario->tareas->first());
    }

    public function test_usuario_es_admin(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'admin']);
        $usuario = Usuario::factory()->create(['rol' => 'usuario']);

        $this->assertTrue($admin->esAdmin());
        $this->assertFalse($usuario->esAdmin());
    }

    public function test_usuario_es_usuario_regular(): void
    {
        $admin = Usuario::factory()->create(['rol' => 'admin']);
        $usuario = Usuario::factory()->create(['rol' => 'usuario']);

        $this->assertFalse($admin->esUsuario());
        $this->assertTrue($usuario->esUsuario());
    }

    public function test_usuario_por_defecto_tiene_rol_usuario(): void
    {
        $usuario = Usuario::factory()->create();

        $this->assertEquals('usuario', $usuario->rol);
        $this->assertTrue($usuario->esUsuario());
    }
}
