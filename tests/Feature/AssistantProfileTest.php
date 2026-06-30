<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_assistant_profile_uses_assistant_interface(): void
    {
        $assistant = User::factory()->create([
            'name' => 'Asistente Recepcion',
            'email' => 'asistente@example.test',
            'telefono' => '222 111 0000',
            'puesto' => 'Recepcion y gestion de citas',
        ]);

        $this->withSession([
            'access_role' => 'asistente',
            'access_user_id' => $assistant->id,
            'access_email' => $assistant->email,
            'access_name' => $assistant->name,
        ])->get(route('perfil'))
            ->assertOk()
            ->assertSee('Perfil de asistente')
            ->assertSee('Recepcion y operacion')
            ->assertSee('Recepcion y gestion de citas')
            ->assertDontSee('Odontologia general');
    }

    public function test_assistant_can_update_own_profile(): void
    {
        $assistant = User::factory()->create([
            'name' => 'Asistente Viejo',
            'email' => 'viejo@example.test',
            'telefono' => 'No registrado',
            'puesto' => 'Recepcion',
        ]);

        $this->withSession([
            'access_role' => 'asistente',
            'access_user_id' => $assistant->id,
            'access_email' => $assistant->email,
            'access_name' => $assistant->name,
        ])->post(route('perfil.update'), [
            'nombre' => 'Asistente Nuevo',
            'email' => 'nuevo@example.test',
            'telefono' => '222 333 4444',
            'especialidad' => 'Recepcion principal',
        ])->assertRedirect(route('perfil'));

        $this->assertDatabaseHas('users', [
            'id' => $assistant->id,
            'name' => 'Asistente Nuevo',
            'email' => 'nuevo@example.test',
            'telefono' => '222 333 4444',
            'puesto' => 'Recepcion principal',
        ]);
    }
}
