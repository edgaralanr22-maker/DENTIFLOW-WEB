<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_use_real_period_data(): void
    {
        $doctor = User::create([
            'name' => 'Dra. Reporte',
            'email' => 'dra.reporte@dentiflow.test',
            'role' => 'doctor',
            'password' => 'password',
        ]);
        $patient = Patient::create(['nombre' => 'Paciente reporte', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $dentist = Dentist::create(['user_id' => $doctor->id, 'nombre' => 'Dra. Reporte', 'telefono' => '2220000000']);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Limpieza',
            'estado' => 'Asistida',
        ]);
        Treatment::create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'paciente' => $patient->nombre,
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
            'estado' => 'Realizado',
            'costo' => 800,
        ]);

        $response = $this->withSession([
                'access_role' => 'doctor',
                'access_user_id' => $doctor->id,
                'access_email' => $doctor->email,
                'access_name' => $doctor->name,
            ])
            ->get(route('reportes', ['periodo' => 'Este mes']));

        $response->assertOk();
        $response->assertSee('Ganancias');
        $response->assertSee('$800');
        $response->assertSee('Dra. Reporte');
        $response->assertSee('Preventivo');
    }

    public function test_selected_month_updates_all_report_data(): void
    {
        $doctor = User::create([
            'name' => 'Dra. Periodo',
            'email' => 'dra.periodo@dentiflow.test',
            'role' => 'doctor',
            'password' => 'password',
        ]);
        $patient = Patient::create(['nombre' => 'Paciente mensual', 'telefono' => '2220000010', 'estado' => 'Activo']);
        $dentist = Dentist::create(['user_id' => $doctor->id, 'nombre' => 'Dra. Periodo', 'telefono' => '2220000011']);

        foreach (['2026-04-15' => ['Abril', 450], '2026-05-15' => ['Mayo', 900]] as $date => [$name, $cost]) {
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'date' => $date,
                'time' => '10:00:00',
                'tipo' => $name,
                'estado' => 'Asistida',
            ]);
            Treatment::create([
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
                'paciente' => $patient->nombre,
                'tratamiento' => $name,
                'tipo' => 'Mensual',
                'fecha' => $date,
                'estado' => 'Realizado',
                'costo' => $cost,
            ]);
        }

        $response = $this->withSession([
            'access_role' => 'doctor',
            'access_user_id' => $doctor->id,
            'access_email' => $doctor->email,
        ])->get(route('reportes', [
            'periodo' => 'Personalizado',
            'desde' => '2026-04',
            'hasta' => '2026-04',
        ]));

        $response->assertOk();
        $response->assertSee('Abril');
        $response->assertSee('$450');
        $response->assertDontSee('Mayo');
        $response->assertDontSee('$900');
    }

    public function test_reports_do_not_show_mock_distribution_without_data(): void
    {
        $response = $this->withSession(['access_role' => 'doctor'])->get(route('reportes', ['periodo' => 'Este mes']));

        $response->assertOk();
        $response->assertDontSee('Limpieza Dental');
        $response->assertDontSee('Ortodoncia');
    }

    public function test_report_export_returns_csv_with_real_rows(): void
    {
        $doctor = User::create([
            'name' => 'Dra. Export',
            'email' => 'dra.export@dentiflow.test',
            'role' => 'doctor',
            'password' => 'password',
        ]);
        $dentist = Dentist::create(['user_id' => $doctor->id, 'nombre' => 'Dra. Export', 'telefono' => '2220000000']);
        $patient = Patient::create(['nombre' => 'Paciente export', 'telefono' => '2220000000', 'estado' => 'Activo']);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Consulta',
            'estado' => 'Asistida',
        ]);
        Treatment::create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'paciente' => $patient->nombre,
            'tratamiento' => 'Consulta',
            'tipo' => 'Diagnóstico',
            'fecha' => now()->toDateString(),
            'estado' => 'Realizado',
            'costo' => 500,
        ]);

        $response = $this->withSession([
                'access_role' => 'doctor',
                'access_user_id' => $doctor->id,
                'access_email' => $doctor->email,
                'access_name' => $doctor->name,
            ])
            ->get(route('reportes.export', ['periodo' => 'Este mes', 'type' => 'excel']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Paciente export', $csv);
        $this->assertStringContainsString('Consulta', $csv);
    }

    public function test_doctor_reports_are_scoped_to_own_realized_treatment_costs(): void
    {
        $doctor = User::create([
            'name' => 'Dra. Reporte Propio',
            'email' => 'reporte.propio@dentiflow.test',
            'role' => 'doctor',
            'password' => 'password',
        ]);
        $ownDentist = Dentist::create(['user_id' => $doctor->id, 'nombre' => 'Dra. Reporte Propio', 'telefono' => '2220000000']);
        $otherDentist = Dentist::create(['nombre' => 'Dr. Reporte Ajeno', 'telefono' => '2220000001']);
        $patient = Patient::create(['nombre' => 'Paciente Reporte Propio', 'telefono' => '2220000002', 'estado' => 'Activo']);
        $otherPatient = Patient::create(['nombre' => 'Paciente Reporte Ajeno', 'telefono' => '2220000003', 'estado' => 'Activo']);

        $ownAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $ownDentist->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'tipo' => 'Limpieza',
            'estado' => 'Asistida',
        ]);
        $otherAppointment = Appointment::create([
            'patient_id' => $otherPatient->id,
            'dentist_id' => $otherDentist->id,
            'date' => now()->toDateString(),
            'time' => '11:00:00',
            'tipo' => 'Implante',
            'estado' => 'Asistida',
        ]);

        Treatment::create([
            'patient_id' => $patient->id,
            'appointment_id' => $ownAppointment->id,
            'paciente' => $patient->nombre,
            'tratamiento' => 'Limpieza',
            'tipo' => 'Preventivo',
            'fecha' => now()->toDateString(),
            'estado' => 'Realizado',
            'costo' => 700,
        ]);
        Treatment::create([
            'patient_id' => $otherPatient->id,
            'appointment_id' => $otherAppointment->id,
            'paciente' => $otherPatient->nombre,
            'tratamiento' => 'Implante',
            'tipo' => 'Quirurgico',
            'fecha' => now()->toDateString(),
            'estado' => 'Realizado',
            'costo' => 1500,
        ]);

        $response = $this->withSession([
                'access_role' => 'doctor',
                'access_user_id' => $doctor->id,
                'access_email' => $doctor->email,
                'access_name' => $doctor->name,
            ])
            ->get(route('reportes', ['periodo' => 'Este mes']));

        $response->assertOk();
        $response->assertSee('$700');
        $response->assertSee('Limpieza');
        $response->assertDontSee('$1.500');
        $response->assertDontSee('Implante');
    }
}
