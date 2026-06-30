<?php

namespace Tests\Feature;

use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentDentistSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_uses_the_dentist_from_the_active_doctor_session(): void
    {
        $user = User::factory()->create(['name' => 'Dra. Sesión Activa']);
        $activeDentist = Dentist::create([
            'user_id' => $user->id,
            'nombre' => $user->name,
            'telefono' => '2220000000',
        ]);
        Treatment::create([
            'paciente' => 'Paciente base',
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
        ]);
        Patient::create(['nombre' => 'Paciente nuevo', 'telefono' => '2220000000', 'estado' => 'Activo']);

        $response = $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => $user->id,
            'access_name' => $user->name,
            'access_email' => $user->email,
        ])->post(route('citas.store'), [
            'paciente' => 'Paciente nuevo',
            'dentista' => 'Dentista manipulado',
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '10:00',
            'tipo' => 'Limpieza',
        ]);

        $response->assertRedirect(route('citas'));
        $this->assertDatabaseHas('appointments', [
            'dentist_id' => $activeDentist->id,
            'tipo' => 'Limpieza',
        ]);
        $this->assertDatabaseMissing('dentists', ['nombre' => 'Dentista manipulado']);
    }

    public function test_doctor_session_without_existing_user_creates_linked_dentist(): void
    {
        Treatment::create([
            'paciente' => 'Paciente base',
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
        ]);
        Patient::create(['nombre' => 'Paciente nuevo', 'telefono' => '2220000000', 'estado' => 'Activo']);

        $response = $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => null,
            'access_name' => null,
            'access_email' => 'doctor.nuevo@example.test',
        ])->post(route('citas.store'), [
            'paciente' => 'Paciente nuevo',
            'dentista' => 'Dentista ignorado',
            'fecha' => now()->addDay()->toDateString(),
            'hora' => '10:00',
            'tipo' => 'Limpieza',
        ]);

        $response->assertRedirect(route('citas'));
        $this->assertDatabaseHas('users', ['email' => 'doctor.nuevo@example.test']);
        $this->assertDatabaseHas('dentists', ['nombre' => 'doctor.nuevo']);
        $this->assertDatabaseMissing('dentists', ['nombre' => 'Dentista ignorado']);
    }

    public function test_appointment_cannot_overlap_existing_dentist_block(): void
    {
        $patient = Patient::create(['nombre' => 'Paciente existente', 'telefono' => '', 'estado' => 'Activo']);
        Patient::create(['nombre' => 'Paciente nuevo', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['nombre' => 'Dra. Agenda', 'telefono' => '2220000000']);
        Treatment::create([
            'paciente' => 'Paciente base',
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
        ]);
        Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => '2026-07-01',
            'time' => '10:00:00',
            'tipo' => 'Limpieza',
            'estado' => 'Pendiente',
        ]);

        $response = $this->withSession([
            'access_role' => 'asistente',
        ])->post(route('citas.store'), [
            'paciente' => 'Paciente nuevo',
            'dentista' => $dentist->nombre,
            'fecha' => '2026-07-01',
            'hora' => '10:30',
            'tipo' => 'Limpieza',
        ]);

        $response->assertSessionHasErrors('hora');
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_appointment_respects_disabled_dentist_schedule_day(): void
    {
        $patient = Patient::create(['nombre' => 'Paciente agenda', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['nombre' => 'Dra. Sin sabado', 'telefono' => '2220000000']);
        Treatment::create([
            'paciente' => $patient->nombre,
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
        ]);
        DentistSchedule::create([
            'dentist_id' => $dentist->id,
            'weekday' => 6,
            'enabled' => false,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $saturday = now()->next('Saturday')->toDateString();

        $response = $this->withSession(['access_role' => 'asistente'])->post(route('citas.store'), [
            'paciente' => $patient->nombre,
            'dentista' => $dentist->nombre,
            'fecha' => $saturday,
            'hora' => '10:00',
            'tipo' => 'Limpieza',
        ]);

        $response->assertSessionHasErrors('hora');
        $this->assertDatabaseMissing('appointments', ['dentist_id' => $dentist->id, 'date' => $saturday]);
    }

    public function test_appointment_must_fit_inside_dentist_schedule(): void
    {
        $patient = Patient::create(['nombre' => 'Paciente horario', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['nombre' => 'Dra. Horario', 'telefono' => '2220000000']);
        Treatment::create([
            'paciente' => $patient->nombre,
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
        ]);

        $date = now()->next('Monday')->toDateString();
        DentistSchedule::create([
            'dentist_id' => $dentist->id,
            'weekday' => 1,
            'enabled' => true,
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $response = $this->withSession(['access_role' => 'asistente'])->post(route('citas.store'), [
            'paciente' => $patient->nombre,
            'dentista' => $dentist->nombre,
            'fecha' => $date,
            'hora' => '10:30',
            'tipo' => 'Limpieza',
        ]);

        $response->assertSessionHasErrors('hora');
        $this->assertDatabaseMissing('appointments', ['dentist_id' => $dentist->id, 'date' => $date]);
    }

    public function test_reprogram_route_redirects_to_edit_form(): void
    {
        $patient = Patient::create(['nombre' => 'Paciente reprograma', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['nombre' => 'Dra. Reprograma', 'telefono' => '2220000000']);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Limpieza',
            'estado' => 'Pendiente',
        ]);

        $response = $this->withSession(['access_role' => 'asistente'])->get(route('citas.reprogramar', $appointment));

        $response->assertRedirect(route('citas.edit', ['id' => $appointment->id]));
    }

    public function test_finishing_appointment_registers_doctor_earnings_once(): void
    {
        $user = User::factory()->create(['name' => 'Dra. Ganancias', 'role' => 'doctor']);
        $dentist = Dentist::create([
            'user_id' => $user->id,
            'nombre' => $user->name,
            'telefono' => '2220000090',
        ]);
        $patient = Patient::create([
            'nombre' => 'Paciente terminado',
            'telefono' => '2220000091',
            'estado' => 'Activo',
        ]);
        Treatment::create([
            'paciente' => '',
            'tratamiento' => 'Limpieza final',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
            'estado' => 'Activo',
            'costo' => 650,
        ]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Limpieza final',
            'estado' => 'Confirmada',
        ]);
        $session = [
            'access_role' => 'doctor',
            'access_user_id' => $user->id,
            'access_email' => $user->email,
        ];

        $this->withSession($session)->post(route('citas.terminar', $appointment))
            ->assertRedirect(route('citas'));
        $this->withSession($session)->post(route('citas.terminar', $appointment))
            ->assertRedirect(route('citas'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'estado' => 'Terminada',
        ]);
        $this->assertDatabaseHas('treatments', [
            'appointment_id' => $appointment->id,
            'estado' => 'Realizado',
            'costo' => 650,
        ]);
        $this->assertSame(1, Treatment::where('appointment_id', $appointment->id)->count());

        $this->withSession($session)
            ->get(route('reportes', ['periodo' => 'Este mes']))
            ->assertOk()
            ->assertSee('$650');
    }

    public function test_doctor_only_sees_own_appointments_in_appointments_section(): void
    {
        $user = User::factory()->create(['name' => 'Dra. Agenda Privada', 'role' => 'doctor']);
        $ownDentist = Dentist::create([
            'user_id' => $user->id,
            'nombre' => $user->name,
            'telefono' => '2220000080',
        ]);
        $otherDentist = Dentist::create([
            'nombre' => 'Dr. Agenda Ajena',
            'telefono' => '2220000081',
        ]);
        $ownPatient = Patient::create(['nombre' => 'Paciente propio visible', 'telefono' => '2220000082', 'estado' => 'Activo']);
        $otherPatient = Patient::create(['nombre' => 'Paciente ajeno oculto', 'telefono' => '2220000083', 'estado' => 'Activo']);

        Appointment::create([
            'patient_id' => $ownPatient->id,
            'dentist_id' => $ownDentist->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Consulta propia',
            'estado' => 'Confirmada',
        ]);
        Appointment::create([
            'patient_id' => $otherPatient->id,
            'dentist_id' => $otherDentist->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '11:00:00',
            'tipo' => 'Consulta ajena',
            'estado' => 'Confirmada',
        ]);

        $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => $user->id,
            'access_email' => $user->email,
        ])->get(route('citas'))
            ->assertOk()
            ->assertSee('Paciente propio visible')
            ->assertDontSee('Paciente ajeno oculto')
            ->assertDontSee('Dr. Agenda Ajena');
    }
}
