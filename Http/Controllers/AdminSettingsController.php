<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClinicSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'settings' => ClinicSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'clinic_name' => 'required|string|max:100',
            'support_email' => 'required|email|max:150',
            'appointment_duration' => 'required|integer|in:30,45,60,90',
            'schedule_interval' => 'required|integer|in:15,30,60',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'default_appointment_status' => 'required|string|in:Pendiente,Confirmada',
            'modules' => 'array',
            'modules.*' => 'integer|between:0,3',
        ]);

        $enabledModules = collect($request->input('modules', []))
            ->map(fn ($module) => (int) $module)
            ->all();

        $previousSettings = ClinicSetting::current();
        $previous = $previousSettings->exists ? $previousSettings->only([
            'clinic_name',
            'support_email',
            'appointment_duration',
            'schedule_interval',
            'opening_time',
            'closing_time',
            'default_appointment_status',
            'maintenance_mode_enabled',
            'administrative_audit_enabled',
        ]) : [];

        $settings = ClinicSetting::upsertCurrent([
            'clinic_name' => $data['clinic_name'],
            'support_email' => $data['support_email'],
            'appointment_duration' => $data['appointment_duration'],
            'schedule_interval' => $data['schedule_interval'],
            'opening_time' => $data['opening_time'],
            'closing_time' => $data['closing_time'],
            'default_appointment_status' => $data['default_appointment_status'],
            // El front actual envia indices de checkbox; aqui se guardan como banderas explicitas.
            'automatic_reminders_enabled' => in_array(0, $enabledModules, true),
            'inventory_alerts_enabled' => in_array(1, $enabledModules, true),
            'maintenance_mode_enabled' => in_array(2, $enabledModules, true),
            'administrative_audit_enabled' => in_array(3, $enabledModules, true),
        ]);

        AuditLog::record('admin.settings.updated', $settings, [
            'before' => $previous,
            'after' => $settings->only([
                'clinic_name',
                'support_email',
                'appointment_duration',
                'schedule_interval',
                'opening_time',
                'closing_time',
                'default_appointment_status',
                'maintenance_mode_enabled',
                'administrative_audit_enabled',
            ]),
        ], $request, (bool) ($previous['administrative_audit_enabled'] ?? false) || $settings->administrative_audit_enabled);

        return back()->with('success', 'La configuracion se guardo correctamente.');
    }
}
