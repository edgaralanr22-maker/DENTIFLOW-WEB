<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_registered_users_section_without_create_form(): void
    {
        User::factory()->create(['name' => 'Usuario Registrado', 'email' => 'usuario@example.test', 'role' => 'doctor']);

        $this->withSession(['access_role' => 'admin'])
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Usuarios del sistema')
            ->assertSee('Usuario Registrado')
            ->assertDontSee('Crear usuario')
            ->assertDontSee('name="role"', false)
            ->assertDontSee('admin.users.store');
    }

    public function test_admin_cannot_create_users_from_users_section(): void
    {
        $this->withSession(['access_role' => 'admin'])
            ->post('/administracion/usuarios', [
                'name' => 'Usuario Nuevo',
                'email' => 'nuevo@example.test',
                'role' => 'doctor',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertStatus(405);

        $this->assertDatabaseMissing('users', ['email' => 'nuevo@example.test']);
    }

    public function test_admin_can_update_registered_user_basic_data(): void
    {
        $user = User::factory()->create(['name' => 'Nombre Anterior', 'email' => 'anterior@example.test', 'role' => 'doctor']);

        $this->withSession(['access_role' => 'admin'])
            ->put(route('admin.users.update', $user), [
                'name' => 'Nombre Actualizado',
                'email' => 'actualizado@example.test',
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nombre Actualizado',
            'email' => 'actualizado@example.test',
            'role' => 'doctor',
        ]);
    }
}
