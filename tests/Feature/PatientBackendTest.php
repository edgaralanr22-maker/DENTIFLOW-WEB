<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_be_created_without_last_visit_and_is_shown_as_new(): void
    {
        $this->withSession(['access_role' => 'doctor'])
            ->post(route('pacientes.store'), [
                'nombre' => 'Paciente Sin Visita',
                'telefono' => '2220000000',
                'email' => '',
                'estado' => 'Activo',
            ])
            ->assertRedirect(route('pacientes'));

        $this->assertDatabaseHas('patients', [
            'nombre' => 'Paciente Sin Visita',
            'ultima_visita' => null,
        ]);

        $this->withSession(['access_role' => 'doctor'])
            ->get(route('pacientes'))
            ->assertOk()
            ->assertSee('Paciente Sin Visita')
            ->assertSee('Nuevo');
    }

    public function test_patient_duplicate_email_uses_readable_validation_message(): void
    {
        Patient::create([
            'nombre' => 'Paciente Existente',
            'telefono' => '2220000000',
            'email' => 'paciente@example.test',
            'estado' => 'Activo',
        ]);

        $response = $this->withSession(['access_role' => 'doctor'])
            ->from(route('pacientes.create'))
            ->post(route('pacientes.store'), [
                'nombre' => 'Paciente Duplicado',
                'telefono' => '2220000001',
                'email' => 'paciente@example.test',
                'estado' => 'Activo',
            ]);

        $response->assertRedirect(route('pacientes.create'));
        $response->assertSessionHasErrors(['email' => 'Ese correo ya esta registrado en otro paciente.']);
    }
}
