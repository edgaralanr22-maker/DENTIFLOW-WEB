<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarAppointmentLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_month_calendar_appointment_opens_detail_view(): void
    {
        $patient = Patient::create([
            'nombre' => 'Paciente Calendario',
            'telefono' => '2220000000',
            'estado' => 'Activo',
        ]);
        $dentist = Dentist::create([
            'nombre' => 'Dra. Calendario',
            'telefono' => '2220000001',
        ]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->startOfMonth()->toDateString(),
            'time' => '13:00:00',
            'tipo' => 'Consulta',
            'estado' => 'Pendiente',
        ]);

        $this->withSession(['access_role' => 'doctor'])
            ->get(route('calendario', [
                'view' => 'mes',
                'year' => now()->year,
                'month' => now()->month,
            ]))
            ->assertOk()
            ->assertSee(route('citas.show', ['id' => $appointment->id]), false)
            ->assertSee('Ver detalle de cita de Paciente Calendario');
    }
}
