<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->query('periodo', 'Ultimos 6 meses');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        [$from, $to, $periodoLabel, $hasData] = $this->resolvePeriod($periodo, $desde, $hasta);
        $isDoctor = $request->session()->get('access_role') === 'doctor';
        $doctorDentist = $isDoctor ? $this->sessionDentist($request) : null;

        $appointments = $this->scopeAppointmentsForRole(
            Appointment::with('dentist')
                ->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to),
            $isDoctor,
            $doctorDentist
        )->get();

        $treatments = $this->scopeTreatmentsForRole(
            Treatment::with(['patient', 'appointment.dentist'])
                ->whereDate('fecha', '>=', $from)
                ->whereDate('fecha', '<=', $to),
            $isDoctor,
            $doctorDentist
        )->get();

        $patientsCount = $appointments->pluck('patient_id')
            ->merge($treatments->pluck('patient_id'))
            ->filter()
            ->unique()
            ->count();

        $ganancias = $treatments->where('estado', 'Realizado')->sum('costo');
        $citas = $appointments->count();
        $tratamientos = $treatments->count();

        $previousRange = $this->previousPeriodRange($from, $to);
        $previousAppointmentsCollection = $this->scopeAppointmentsForRole(
            Appointment::whereDate('date', '>=', $previousRange[0])
                ->whereDate('date', '<=', $previousRange[1]),
            $isDoctor,
            $doctorDentist
        )->get();
        $previousTreatmentsCollection = $this->scopeTreatmentsForRole(
            Treatment::whereDate('fecha', '>=', $previousRange[0])
                ->whereDate('fecha', '<=', $previousRange[1]),
            $isDoctor,
            $doctorDentist
        )->get();
        $previousAppointments = $previousAppointmentsCollection->count();
        $previousTreatments = $previousTreatmentsCollection->count();
        $previousGanancias = $previousTreatmentsCollection->where('estado', 'Realizado')->sum('costo');
        $previousPatients = $previousAppointmentsCollection->pluck('patient_id')
            ->merge($previousTreatmentsCollection->pluck('patient_id'))
            ->filter()
            ->unique()
            ->count();

        $reportes = [
            'ganancias' => ['display' => '$' . number_format($ganancias, 0, ',', '.')],
            'pacientes' => ['display' => $patientsCount],
            'citas' => ['display' => $citas],
            'tratamientos' => ['display' => $tratamientos],
        ];

        $trends = [
            'ganancias' => $this->buildTrend($ganancias, $previousGanancias, 'Ganancias'),
            'pacientes' => $this->buildTrend($patientsCount, $previousPatients, 'Pacientes'),
            'citas' => $this->buildTrend($citas, $previousAppointments, 'Citas'),
            'tratamientos' => $this->buildTrend($tratamientos, $previousTreatments, 'Tratamientos'),
        ];

        $distribution = $treatments
            ->groupBy('tipo')
            ->map(fn ($items, $tipo) => [
                'tratamiento' => $tipo,
                'porcentaje' => (int) round(($items->count() / max($tratamientos, 1)) * 100),
            ])
            ->values()
            ->all();

        $treatmentCostBreakdown = $treatments
            ->where('estado', 'Realizado')
            ->groupBy('tratamiento')
            ->map(fn ($items, $name) => [
                'tratamiento' => $name,
                'cantidad' => $items->count(),
                'total' => (float) $items->sum('costo'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();

        $topDentistas = $appointments
            ->groupBy(fn ($appointment) => $appointment->dentist?->nombre ?? 'Sin dentista')
            ->map(function ($appointments, $dentista) use ($treatments) {
                $appointmentIds = $appointments->pluck('id')->all();
                $ganancias = $treatments
                    ->whereIn('appointment_id', $appointmentIds)
                    ->where('estado', 'Realizado')
                    ->sum('costo');

                return [
                    'dentista' => $dentista,
                    'citas' => $appointments->count(),
                    'ganancias' => $ganancias,
                ];
            })
            ->sortByDesc('citas')
            ->values()
            ->all();

        $grafico = $this->buildMonthlyEarnings($from, $to, $treatments);
        $maxRevenue = max(1, collect($grafico)->max('valor') ?: 0);
        $appointmentGrowth = $this->buildTrend($citas, $previousAppointments, 'Citas');
        $quickReadings = [
            // Lecturas derivadas del periodo actual; evitan valores fijos en el reporte.
            'averageEarningsPerAppointment' => $citas > 0 ? round($ganancias / $citas) : 0,
            'monthlyAppointmentGrowth' => ($appointmentGrowth['positive'] ? '+' : '-').$appointmentGrowth['percent'].'%',
            'activeTreatments' => Treatment::where('estado', 'Activo')->count(),
        ];

        return view('reportes.index', compact(
            'reportes',
            'trends',
            'grafico',
            'maxRevenue',
            'quickReadings',
            'distribution',
            'topDentistas',
            'treatmentCostBreakdown',
            'hasData',
            'periodo',
            'desde',
            'hasta'
        ));
    }

    public function export(Request $request)
    {
        $periodo = $request->query('periodo', 'Ultimos 6 meses');
        [$from, $to] = $this->resolvePeriod($periodo, $request->query('desde'), $request->query('hasta'));

        $isDoctor = $request->session()->get('access_role') === 'doctor';
        $doctorDentist = $isDoctor ? $this->sessionDentist($request) : null;

        $rows = $this->scopeTreatmentsForRole(
            Treatment::with(['patient', 'appointment.dentist'])
                ->whereDate('fecha', '>=', $from)
                ->whereDate('fecha', '<=', $to)
                ->orderBy('fecha'),
            $isDoctor,
            $doctorDentist
        )
            ->get()
            ->map(fn (Treatment $treatment) => [
                'fecha' => $treatment->fecha?->toDateString(),
                'paciente' => $treatment->patient?->nombre ?? $treatment->paciente,
                'tratamiento' => $treatment->tratamiento,
                'tipo' => $treatment->tipo,
                'estado' => $treatment->estado,
                'costo' => (float) $treatment->costo,
                'dentista' => $treatment->appointment?->dentist?->nombre ?? 'Sin dentista',
            ]);

        $filename = 'reporte-dentiflow-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['fecha', 'paciente', 'tratamiento', 'tipo', 'estado', 'costo', 'dentista']);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function resolvePeriod(string $periodo, ?string $desde, ?string $hasta): array
    {
        $today = Carbon::today();
        $from = $today->copy()->subMonths(5)->startOfMonth();
        $to = $today->copy()->endOfMonth();

        switch ($periodo) {
            case 'Este mes':
                $from = $today->copy()->startOfMonth();
                $to = $today->copy()->endOfMonth();
                break;
            case 'Ultimos 3 meses':
            case 'Últimos 3 meses':
                $from = $today->copy()->subMonths(2)->startOfMonth();
                break;
            case 'Ultimos 6 meses':
            case 'Últimos 6 meses':
                $from = $today->copy()->subMonths(5)->startOfMonth();
                break;
            case 'Este ano':
            case 'Este año':
                $from = $today->copy()->startOfYear();
                break;
            case 'Personalizado':
                try {
                    $customFrom = $desde ? Carbon::createFromFormat('Y-m', $desde)->startOfMonth() : null;
                    $customTo = $hasta ? Carbon::createFromFormat('Y-m', $hasta)->endOfMonth() : null;
                } catch (\Throwable) {
                    $customFrom = null;
                    $customTo = null;
                }

                if ($customFrom && $customTo && $customFrom->lte($customTo)) {
                    $from = $customFrom;
                    $to = $customTo;
                } else {
                    return [$from->toDateString(), $to->toDateString(), $periodo, false];
                }
                break;
        }

        return [$from->toDateString(), $to->toDateString(), $periodo, true];
    }

    private function previousPeriodRange(string $from, string $to): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $from);
        $end = Carbon::createFromFormat('Y-m-d', $to);
        $interval = $start->diffInDays($end) + 1;

        $previousEnd = $start->copy()->subDay();
        $previousStart = $previousEnd->copy()->subDays($interval - 1);

        return [$previousStart->toDateString(), $previousEnd->toDateString()];
    }

    private function buildTrend(int|float $current, int|float $previous, string $label): array
    {
        if ((float) $previous === 0.0) {
            return [
                'arrow' => $current >= 0 ? '▲' : '▼',
                'percent' => $current == 0 ? 0 : 100,
                'positive' => $current >= 0,
                'label' => $label,
            ];
        }

        $percent = round((($current - $previous) / max($previous, 1)) * 100);

        return [
            'arrow' => $current >= $previous ? '▲' : '▼',
            'percent' => abs($percent),
            'positive' => $current >= $previous,
            'label' => $label,
        ];
    }

    private function buildMonthlyEarnings(string $from, string $to, $treatments): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $from)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m-d', $to)->endOfMonth();
        $months = [];

        while ($start->lte($end)) {
            $months[$start->format('Y-m')] = [
                'mes' => $start->translatedFormat('M'),
                'valor' => 0,
            ];
            $start->addMonth();
        }

        foreach ($treatments as $treatment) {
            if ($treatment->estado !== 'Realizado' || ! $treatment->fecha) {
                continue;
            }

            $key = $treatment->fecha->format('Y-m');
            if (isset($months[$key])) {
                $months[$key]['valor'] += (float) $treatment->costo;
            }
        }

        return array_values($months);
    }

    private function sessionDentist(Request $request): ?Dentist
    {
        $user = User::find($request->session()->get('access_user_id'))
            ?? User::where('email', $request->session()->get('access_email'))->first();

        if (! $user) {
            return null;
        }

        return Dentist::where('user_id', $user->id)->first()
            ?? Dentist::whereNull('user_id')->where('nombre', $user->name)->first();
    }

    private function scopeAppointmentsForRole($query, bool $isDoctor, ?Dentist $dentist)
    {
        if ($isDoctor) {
            $query->where('dentist_id', $dentist?->id ?? 0);
        }

        return $query;
    }

    private function scopeTreatmentsForRole($query, bool $isDoctor, ?Dentist $dentist)
    {
        if ($isDoctor) {
            $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('dentist_id', $dentist?->id ?? 0));
        }

        return $query;
    }
}
