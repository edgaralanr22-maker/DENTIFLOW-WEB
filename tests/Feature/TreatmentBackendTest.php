<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_treatment_can_be_created_with_valid_data(): void
    {
        $response = $this->withSession(['access_role' => 'doctor'])->post(route('pacientes.tratamientos.store'), [
            'tratamiento' => 'Limpieza profunda',
            'tipo' => 'Preventivo',
            'costo' => 750,
            'descripcion' => 'Servicio preventivo completo.',
        ]);

        $response->assertRedirect(route('pacientes.tratamientos'));
        $this->assertDatabaseHas('treatments', [
            'tratamiento' => 'Limpieza profunda',
            'tipo' => 'Preventivo',
            'estado' => 'Activo',
        ]);
    }

    public function test_catalog_treatment_name_must_be_unique(): void
    {
        Treatment::create([
            'paciente' => '',
            'tratamiento' => 'Ortodoncia',
            'tipo' => 'Correctivo',
            'fecha' => now()->toDateString(),
            'estado' => 'Activo',
            'costo' => 1200,
        ]);

        $response = $this->withSession(['access_role' => 'doctor'])->post(route('pacientes.tratamientos.store'), [
            'tratamiento' => 'Ortodoncia',
            'tipo' => 'Correctivo',
            'costo' => 1300,
        ]);

        $response->assertSessionHasErrors('tratamiento');
    }

    public function test_treatment_used_by_appointment_cannot_be_deleted(): void
    {
        $treatment = Treatment::create([
            'paciente' => '',
            'tratamiento' => 'Consulta inicial',
            'tipo' => 'Diagnóstico',
            'fecha' => now()->toDateString(),
            'estado' => 'Activo',
            'costo' => 500,
        ]);
        $patient = Patient::create(['nombre' => 'Paciente tratamiento', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['nombre' => 'Dra. Tratamiento', 'telefono' => '2220000000']);
        Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00:00',
            'tipo' => $treatment->tratamiento,
            'estado' => 'Pendiente',
        ]);

        $response = $this->withSession(['access_role' => 'doctor'])->get(route('pacientes.tratamientos.delete', $treatment->id));

        $response->assertRedirect(route('pacientes.tratamientos'));
        $response->assertSessionHasErrors('tratamiento');
        $this->assertDatabaseHas('treatments', ['id' => $treatment->id]);
    }

    public function test_treatment_can_be_updated_without_failing_own_unique_name(): void
    {
        $treatment = Treatment::create([
            'paciente' => '',
            'tratamiento' => 'Blanqueamiento',
            'tipo' => 'Estético',
            'fecha' => now()->toDateString(),
            'estado' => 'Activo',
            'costo' => 900,
        ]);

        $response = $this->withSession(['access_role' => 'doctor'])->put(route('pacientes.tratamientos.update', $treatment->id), [
            'tratamiento' => 'Blanqueamiento',
            'tipo' => 'Estético',
            'costo' => 950,
            'descripcion' => 'Actualizado.',
        ]);

        $response->assertRedirect(route('pacientes.tratamientos'));
        $this->assertDatabaseHas('treatments', ['id' => $treatment->id, 'costo' => 950]);
    }
}
