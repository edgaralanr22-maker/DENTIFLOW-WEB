<?php

namespace Tests\Feature;

use App\Models\ClinicSetting;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_settings_show_schedule_operation_configuration(): void
    {
        $this->withSession(['access_role' => 'admin'])
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Agenda y operacion')
            ->assertSee('Duracion por cita')
            ->assertSee('Auditoria administrativa');
    }

    public function test_admin_settings_accept_schedule_operation_configuration(): void
    {
        $this->withSession(['access_role' => 'admin'])
            ->post(route('admin.settings.update'), [
                'clinic_name' => 'Clinica DentiFlow',
                'support_email' => 'soporte@dentiflow.com',
                'appointment_duration' => 60,
                'schedule_interval' => 30,
                'opening_time' => '09:00',
                'closing_time' => '18:00',
                'default_appointment_status' => 'Pendiente',
                'modules' => [0, 1, 3],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clinic_settings', [
            'clinic_name' => 'Clinica DentiFlow',
            'support_email' => 'soporte@dentiflow.com',
            'appointment_duration' => 60,
            'schedule_interval' => 30,
            'default_appointment_status' => 'Pendiente',
            'automatic_reminders_enabled' => true,
            'inventory_alerts_enabled' => true,
            'maintenance_mode_enabled' => false,
            'administrative_audit_enabled' => true,
        ]);
    }

    public function test_admin_settings_are_loaded_into_the_form(): void
    {
        ClinicSetting::create([
            'clinic_name' => 'Clinica Demo',
            'support_email' => 'demo@dentiflow.test',
            'appointment_duration' => 45,
            'schedule_interval' => 15,
            'opening_time' => '08:00',
            'closing_time' => '17:00',
            'default_appointment_status' => 'Confirmada',
        ]);

        $this->withSession(['access_role' => 'admin'])
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Clinica Demo')
            ->assertSee('demo@dentiflow.test')
            ->assertSee('Confirmada');
    }

    public function test_new_appointments_use_configured_default_status(): void
    {
        ClinicSetting::create([
            'clinic_name' => 'Clinica DentiFlow',
            'support_email' => 'soporte@dentiflow.com',
            'appointment_duration' => 30,
            'schedule_interval' => 30,
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'default_appointment_status' => 'Confirmada',
        ]);

        $patient = Patient::create(['nombre' => 'Paciente Config', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['nombre' => 'Dra. Config', 'telefono' => '2220000000']);
        Treatment::create([
            'paciente' => $patient->nombre,
            'tratamiento' => 'Consulta configurada',
            'tipo' => 'Diagnostico',
            'fecha' => now()->toDateString(),
            'estado' => 'Activo',
            'costo' => 500,
        ]);

        $this->withSession(['access_role' => 'asistente'])
            ->post(route('citas.store'), [
                'paciente' => $patient->nombre,
                'dentista' => $dentist->nombre,
                'fecha' => now()->addDay()->toDateString(),
                'hora' => '10:00',
                'tipo' => 'Consulta configurada',
            ])
            ->assertRedirect(route('citas'));

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'estado' => 'Confirmada',
        ]);
    }

    public function test_maintenance_mode_blocks_operational_routes_for_non_admin_users(): void
    {
        ClinicSetting::create([
            'clinic_name' => 'Clinica DentiFlow',
            'support_email' => 'soporte@dentiflow.com',
            'appointment_duration' => 60,
            'schedule_interval' => 30,
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'default_appointment_status' => 'Pendiente',
            'maintenance_mode_enabled' => true,
        ]);

        $this->withSession(['access_role' => 'asistente'])
            ->get(route('citas'))
            ->assertRedirect(route('inicio'))
            ->assertSessionHas('access_denied');
    }

    public function test_admin_can_access_settings_during_maintenance_mode(): void
    {
        ClinicSetting::create([
            'clinic_name' => 'Clinica DentiFlow',
            'support_email' => 'soporte@dentiflow.com',
            'appointment_duration' => 60,
            'schedule_interval' => 30,
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'default_appointment_status' => 'Pendiente',
            'maintenance_mode_enabled' => true,
        ]);

        $this->withSession(['access_role' => 'admin'])
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Modo de mantenimiento');
    }
}
