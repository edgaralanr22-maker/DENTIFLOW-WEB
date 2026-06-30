<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    public const DEFAULTS = [
        'clinic_name' => 'Clinica DentiFlow',
        'support_email' => 'soporte@dentiflow.com',
        'appointment_duration' => 60,
        'schedule_interval' => 30,
        'opening_time' => '09:00',
        'closing_time' => '18:00',
        'default_appointment_status' => 'Pendiente',
        'automatic_reminders_enabled' => true,
        'inventory_alerts_enabled' => true,
        'maintenance_mode_enabled' => false,
        'administrative_audit_enabled' => true,
    ];

    protected $fillable = [
        'clinic_name',
        'support_email',
        'appointment_duration',
        'schedule_interval',
        'opening_time',
        'closing_time',
        'default_appointment_status',
        'automatic_reminders_enabled',
        'inventory_alerts_enabled',
        'maintenance_mode_enabled',
        'administrative_audit_enabled',
    ];

    protected $casts = [
        'appointment_duration' => 'integer',
        'schedule_interval' => 'integer',
        'automatic_reminders_enabled' => 'boolean',
        'inventory_alerts_enabled' => 'boolean',
        'maintenance_mode_enabled' => 'boolean',
        'administrative_audit_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return self::query()->first() ?? new self(self::DEFAULTS);
    }

    public static function upsertCurrent(array $data): self
    {
        $settings = self::query()->first() ?? new self();

        // La aplicacion maneja una sola configuracion global para toda la clinica.
        $settings->fill($data);
        $settings->save();

        return $settings;
    }
}
