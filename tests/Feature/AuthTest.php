<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────── registro

    public function test_al_registrarse_recibe_un_token_y_el_rol_estudiante(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Juan Rios',
            'email' => 'juan@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'status']])
            ->assertJsonPath('user.role', 'student')
            ->assertJsonPath('user.status', 'active');

        $this->assertDatabaseHas('users', ['email' => 'juan@test.com']);
    }

    public function test_el_rol_no_se_puede_elegir_al_registrarse(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Listo',
            'email' => 'listo@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])
            ->assertCreated()
            ->assertJsonPath('user.role', 'student');
    }

    public function test_no_se_puede_repetir_el_correo(): void
    {
        User::factory()->create(['email' => 'ocupado@test.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Otro',
            'email' => 'ocupado@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_la_contrasena_debe_coincidir_y_tener_ocho_caracteres(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Corta',
            'email' => 'corta@test.com',
            'password' => 'abc',
            'password_confirmation' => 'otra',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    // ─────────────────────────────────── login

    public function test_con_credenciales_correctas_devuelve_un_token(): void
    {
        User::factory()->create(['email' => 'ana@test.com']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ana@test.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_con_contrasena_incorrecta_no_entra(): void
    {
        User::factory()->create(['email' => 'ana@test.com']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'ana@test.com',
            'password' => 'equivocada',
        ])->assertUnauthorized();
    }

    public function test_un_correo_inexistente_no_entra(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'nadie@test.com',
            'password' => 'password',
        ])->assertUnauthorized();
    }

    public function test_una_cuenta_inactiva_no_puede_entrar(): void
    {
        User::factory()->create([
            'email' => 'bloqueada@test.com',
            'status' => UserStatus::Inactive,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'bloqueada@test.com',
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Cuenta no activa.');
    }

    // ─────────────────────────────────── me y logout

    public function test_me_devuelve_al_usuario_autenticado(): void
    {
        $user = User::factory()->create(['role' => UserRole::Instructor]);

        $this->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', 'instructor');
    }

    public function test_sin_token_no_se_puede_consultar_me(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_el_logout_revoca_el_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('prueba')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        // El entorno de pruebas conserva el guard ya resuelto entre peticiones
        // de una misma prueba; sin esto reusaria al usuario autenticado antes.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }
}