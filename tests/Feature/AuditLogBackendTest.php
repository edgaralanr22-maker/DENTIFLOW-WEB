<?php

namespace Tests\Feature;

use App\Models\ClinicSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_update_is_audited_when_audit_is_enabled(): void
    {
        $this->withSession(['access_role' => 'admin', 'access_name' => 'Admin Config'])
            ->post(route('admin.settings.update'), [
                'clinic_name' => 'Clinica Auditada',
                'support_email' => 'auditada@dentiflow.test',
                'appointment_duration' => 45,
                'schedule_interval' => 15,
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'default_appointment_status' => 'Confirmada',
                'modules' => [0, 1, 3],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin.settings.updated',
            'entity_type' => ClinicSetting::class,
            'actor_name' => 'Admin Config',
        ]);
    }

    public function test_audit_log_is_not_created_when_disabled(): void
    {
        ClinicSetting::create([
            'clinic_name' => 'Clinica DentiFlow',
            'support_email' => 'soporte@dentiflow.com',
            'appointment_duration' => 60,
            'schedule_interval' => 30,
            'opening_time' => '09:00',
            'closing_time' => '18:00',
            'default_appointment_status' => 'Pendiente',
            'administrative_audit_enabled' => false,
        ]);

        $this->withSession(['access_role' => 'admin'])
            ->post(route('admin.settings.update'), [
                'clinic_name' => 'Clinica Sin Auditoria',
                'support_email' => 'sin.audit@dentiflow.test',
                'appointment_duration' => 60,
                'schedule_interval' => 30,
                'opening_time' => '09:00',
                'closing_time' => '18:00',
                'default_appointment_status' => 'Pendiente',
                'modules' => [0, 1],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('audit_logs', 0);
    }
}
