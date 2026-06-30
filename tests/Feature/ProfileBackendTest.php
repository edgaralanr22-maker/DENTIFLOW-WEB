<?php

namespace Tests\Feature;

use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_update_profile_without_nulling_required_dentist_phone(): void
    {
        $doctor = User::factory()->create([
            'name' => 'Doctor Perfil',
            'email' => 'doctor.perfil@example.test',
            'role' => 'doctor',
        ]);
        $dentist = Dentist::create([
            'user_id' => $doctor->id,
            'nombre' => 'Doctor Perfil',
            'telefono' => '2220000000',
            'especialidad' => 'General',
        ]);

        $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => $doctor->id,
            'access_email' => $doctor->email,
            'access_name' => $doctor->name,
        ])->post(route('perfil.update'), [
            'nombre' => 'Doctor Perfil Nuevo',
            'email' => 'doctor.nuevo@example.test',
            'telefono' => '',
            'especialidad' => '',
        ])->assertRedirect(route('perfil'));

        $this->assertDatabaseHas('users', [
            'id' => $doctor->id,
            'name' => 'Doctor Perfil Nuevo',
            'email' => 'doctor.nuevo@example.test',
        ]);
        $this->assertDatabaseHas('dentists', [
            'id' => $dentist->id,
            'nombre' => 'Doctor Perfil Nuevo',
            'telefono' => 'No registrado',
            'especialidad' => 'Odontologia general',
        ]);
    }

    public function test_doctor_profile_name_must_not_duplicate_another_dentist(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        Dentist::create(['user_id' => $doctor->id, 'nombre' => 'Dra. Propia', 'telefono' => '2220000000']);
        Dentist::create(['nombre' => 'Dra. Ocupada', 'telefono' => '2220000001']);

        $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => $doctor->id,
            'access_email' => $doctor->email,
            'access_name' => $doctor->name,
        ])->post(route('perfil.update'), [
            'nombre' => 'Dra. Ocupada',
            'email' => $doctor->email,
            'telefono' => '2220000000',
            'especialidad' => 'General',
        ])->assertSessionHasErrors('nombre');
    }

    public function test_user_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'asistente',
            'password' => 'Password123!',
        ]);

        $this->withSession([
            'access_role' => 'asistente',
            'access_user_id' => $user->id,
            'access_email' => $user->email,
            'access_name' => $user->name,
        ])->post(route('perfil.password.update'), [
            'current_password' => 'Password123!',
            'password' => 'NuevaPassword123!',
            'password_confirmation' => 'NuevaPassword123!',
        ])->assertRedirect(route('perfil'));

        $this->assertTrue(Hash::check('NuevaPassword123!', $user->fresh()->password));
    }

    public function test_doctor_can_update_weekly_schedule(): void
    {
        $doctor = User::factory()->create(['role' => 'doctor']);
        $dentist = Dentist::create(['user_id' => $doctor->id, 'nombre' => 'Dra. Agenda Perfil', 'telefono' => '2220000000']);
        $schedule = [];

        foreach (range(1, 7) as $weekday) {
            $schedule[$weekday] = [
                'enabled' => $weekday <= 5 ? '1' : '0',
                'start_time' => '08:00',
                'end_time' => $weekday === 5 ? '14:00' : '16:00',
            ];
        }

        $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => $doctor->id,
            'access_email' => $doctor->email,
            'access_name' => $doctor->name,
        ])->post(route('perfil.schedule.update'), [
            'schedule' => $schedule,
        ])->assertRedirect(route('perfil'));

        $this->assertDatabaseHas('dentist_schedules', [
            'dentist_id' => $dentist->id,
            'weekday' => 1,
            'enabled' => true,
            'start_time' => '08:00',
            'end_time' => '16:00',
        ]);
        $this->assertEquals(7, DentistSchedule::where('dentist_id', $dentist->id)->count());
    }
}
