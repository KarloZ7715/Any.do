<?php

namespace Tests\Feature\Auth;

use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('tareas.proximos-siete-dias', absolute: false));
    }

    public function test_new_users_get_personal_category_created(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = auth()->user();
        $categoriaPersonal = Categoria::where('usuario_id', $user->id)
            ->where('es_personal', true)
            ->first();

        $this->assertNotNull($categoriaPersonal);
        $this->assertEquals('Personal', $categoriaPersonal->nombre);
        $this->assertEquals('#6366f1', $categoriaPersonal->color);
        $this->assertEquals('User', $categoriaPersonal->icono);
    }
}
