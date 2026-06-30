<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentistBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_dentist_generates_default_weekly_schedule(): void
    {
        $response = $this->withSession(['access_role' => 'admin'])->post(route('dentistas.store'), [
            'nombre' => 'Dra. Agenda Nueva',
            'especialidad' => 'Ortodoncia',
            'telefono' => '2220000000',
            'email' => 'agenda.nueva@dentiflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('dentistas'));
        $dentist = Dentist::where('nombre', 'Dra. Agenda Nueva')->firstOrFail();

        $this->assertDatabaseCount('dentist_schedules', 7);
        $this->assertDatabaseHas('dentist_schedules', [
            'dentist_id' => $dentist->id,
            'weekday' => 1,
            'enabled' => true,
        ]);
        $this->assertDatabaseHas('dentist_schedules', [
            'dentist_id' => $dentist->id,
            'weekday' => 6,
            'enabled' => false,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $dentist->user_id,
            'email' => 'agenda.nueva@dentiflow.test',
            'role' => 'doctor',
        ]);
    }

    public function test_dentist_requires_unique_name_phone_and_access_credentials(): void
    {
        Dentist::create([
            'nombre' => 'Dr. Repetido',
            'especialidad' => 'General',
            'telefono' => '2220000000',
        ]);

        $response = $this->withSession(['access_role' => 'admin'])->post(route('dentistas.store'), [
            'nombre' => 'Dr. Repetido',
            'especialidad' => 'General',
            'telefono' => '',
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['nombre', 'telefono', 'email', 'password']);
    }

    public function test_new_dentist_can_login_with_created_doctor_account(): void
    {
        $this->withSession(['access_role' => 'admin'])->post(route('dentistas.store'), [
            'nombre' => 'Dra. Acceso Propio',
            'especialidad' => 'Endodoncia',
            'telefono' => '2220000002',
            'email' => 'acceso.propio@dentiflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('dentistas'));

        $doctor = User::where('email', 'acceso.propio@dentiflow.test')->firstOrFail();

        $this->assertDatabaseHas('dentists', [
            'user_id' => $doctor->id,
            'nombre' => 'Dra. Acceso Propio',
        ]);

        $this->post('/login', [
            'role' => 'doctor',
            'email' => 'acceso.propio@dentiflow.test',
            'password' => 'Password123!',
        ])
            ->assertRedirect(route('inicio'))
            ->assertSessionHas('access_role', 'doctor')
            ->assertSessionHas('access_user_id', $doctor->id);
    }

    public function test_existing_dentist_without_user_can_receive_login_access(): void
    {
        $dentist = Dentist::create([
            'nombre' => 'Dr. Sin Acceso',
            'especialidad' => 'General',
            'telefono' => '2220000003',
        ]);

        $this->withSession(['access_role' => 'admin'])->put(route('dentistas.update', $dentist->id), [
            'nombre' => 'Dr. Sin Acceso',
            'especialidad' => 'General',
            'telefono' => '2220000003',
            'email' => 'sin.acceso@dentiflow.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertRedirect(route('dentistas'));

        $doctor = User::where('email', 'sin.acceso@dentiflow.test')->firstOrFail();

        $this->assertDatabaseHas('dentists', [
            'id' => $dentist->id,
            'user_id' => $doctor->id,
        ]);

        $this->post('/login', [
            'role' => 'doctor',
            'email' => 'sin.acceso@dentiflow.test',
            'password' => 'Password123!',
        ])
            ->assertRedirect(route('inicio'))
            ->assertSessionHas('access_user_id', $doctor->id);
    }

    public function test_dentist_with_appointments_cannot_be_deleted(): void
    {
        $patient = Patient::create(['nombre' => 'Paciente dentist', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create([
            'nombre' => 'Dra. Con Citas',
            'especialidad' => 'General',
            'telefono' => '2220000000',
        ]);
        Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Limpieza',
            'estado' => 'Pendiente',
        ]);

        $response = $this->withSession(['access_role' => 'admin'])->get(route('dentistas.delete', ['id' => $dentist->id]));

        $response->assertRedirect(route('dentistas'));
        $response->assertSessionHasErrors('dentista');
        $this->assertDatabaseHas('dentists', ['id' => $dentist->id]);
    }
}
