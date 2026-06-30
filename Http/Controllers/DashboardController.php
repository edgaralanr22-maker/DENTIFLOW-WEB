<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Dentist;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if ($request->session()->get('access_role') === 'admin') {
            $settings = ClinicSetting::current();
            $today = now()->toDateString();
            $selectedDentist = $request->query('dentista', 'Todos');
            $selectedDate = now()->startOfMonth();

            if ($request->filled('year') && $request->filled('month')) {
                $selectedDate = $selectedDate->setDate(
                    max(1970, min(2100, (int) $request->query('year'))),
                    max(1, min(12, (int) $request->query('month'))),
                    1
                );
            }

            $calendarQuery = Appointment::with(['patient', 'dentist'])
                ->whereYear('date', $selectedDate->year)
                ->whereMonth('date', $selectedDate->month);

            if ($selectedDentist !== 'Todos' && $selectedDentist !== '') {
                $calendarQuery->where('dentist_id', $selectedDentist);
            }

            $calendarAppointments = $calendarQuery
                ->orderBy('date')
                ->orderBy('time')
                ->get()
                ->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment))
                ->values()
                ->toArray();

            $calendarData = $this->buildCalendar($selectedDate->toDateString(), $calendarAppointments);
            $appointmentStatus = collect(['Confirmada', 'Pendiente', 'Asistida', 'Terminada', 'Cancelada'])
                ->mapWithKeys(fn (string $status) => [
                    $status => Appointment::where('estado', $status)->count(),
                ]);
            $totalAppointments = max(1, (int) $appointmentStatus->sum());
            $monthlyAppointments = Appointment::whereYear('date', $selectedDate->year)
                ->whereMonth('date', $selectedDate->month)
                ->count();
            $completedTreatments = Treatment::where('estado', 'Realizado')->count();
            $totalTreatments = max(1, Treatment::count());
            $attendanceRate = round(($appointmentStatus['Asistida'] / $totalAppointments) * 100);
            $completionRate = round(($completedTreatments / $totalTreatments) * 100);
            $topDentists = Appointment::with('dentist')
                ->whereDate('date', '>=', now()->copy()->subDays(30)->toDateString())
                ->get()
                ->groupBy(fn (Appointment $appointment) => $appointment->dentist?->nombre ?? 'Sin dentista')
                ->map(fn ($items, $name) => [
                    'name' => $name,
                    'appointments' => $items->count(),
                ])
                ->sortByDesc('appointments')
                ->take(5)
                ->values();

            return view('admin.dashboard', [
                'system' => [
                    'users' => User::count(),
                    'doctors' => Dentist::count(),
                    'lastBackup' => $this->latestSystemUpdate(),
                    'status' => $settings->maintenance_mode_enabled ? 'Modo mantenimiento activo' : 'Operando correctamente',
                ],
                'indicators' => [
                    'appointmentsToday' => Appointment::whereDate('date', $today)->count(),
                    'activePatients' => Patient::where('estado', 'Activo')->count(),
                    'pendingAppointments' => Appointment::where('estado', 'Pendiente')->count(),
                    'registeredTreatments' => Treatment::count(),
                ],
                'dentists' => Dentist::orderBy('nombre')->get(['id', 'nombre']),
                'selectedDentist' => $selectedDentist,
                'selectedDate' => $selectedDate,
                'appointmentsToday' => Appointment::with(['patient', 'dentist'])
                    ->whereDate('date', $today)
                    ->orderBy('time')
                    ->limit(6)
                    ->get()
                    ->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment)),
                'upcomingAppointments' => Appointment::with(['patient', 'dentist'])
                    ->whereDate('date', '>=', $today)
                    ->whereNotIn('estado', ['Cancelada', 'Asistida', 'Terminada'])
                    ->orderBy('date')
                    ->orderBy('time')
                    ->limit(6)
                    ->get()
                    ->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment)),
                'appointmentStatus' => $appointmentStatus,
                'totalAppointments' => $totalAppointments,
                'performance' => [
                    'monthlyAppointments' => $monthlyAppointments,
                    'attendanceRate' => $attendanceRate,
                    'completionRate' => $completionRate,
                    'topDentists' => $topDentists,
                ],
                ...$calendarData,
            ]);
        }

        $view = in_array($request->query('view', 'mes'), ['dia', 'semana', 'mes'], true)
            ? $request->query('view', 'mes')
            : 'mes';

        try {
            $selectedDate = $request->filled('date')
                ? now()->parse($request->query('date'))->startOfDay()
                : now()->startOfDay();
        } catch (\Throwable) {
            $selectedDate = now()->startOfDay();
        }

        if ($view === 'mes' && $request->filled('year') && $request->filled('month')) {
            $selectedDate = $selectedDate->setDate(
                max(1970, min(2100, (int) $request->query('year'))),
                max(1, min(12, (int) $request->query('month'))),
                1
            );
        }

        $today = now()->toDateString();
        $isDoctor = $request->session()->get('access_role') === 'doctor';
        $doctorDentist = $isDoctor ? $this->sessionDentist($request) : null;

        $agendaHoy = $this->scopeAppointmentsForRole(Appointment::with(['patient', 'dentist']), $isDoctor, $doctorDentist)
            ->whereDate('date', $today)
            ->orderBy('time')
            ->get()
            ->map(function (Appointment $appointment) {
                return $this->serializeAppointment($appointment);
            });

        $proximasCitas = $this->scopeAppointmentsForRole(Appointment::with(['patient', 'dentist']), $isDoctor, $doctorDentist)
            ->whereDate('date', '>=', $today)
            ->whereNotIn('estado', ['Cancelada', 'Asistida', 'Terminada'])
            ->orderBy('date')
            ->orderBy('time')
            ->limit(4)
            ->get()
            ->map(function (Appointment $appointment) {
                return $this->serializeAppointment($appointment);
            });

        $tratamientos = $this->scopeTreatmentsForRole(Treatment::with('patient'), $isDoctor, $doctorDentist)
            ->orderByDesc('fecha')
            ->limit(4)
            ->get()
            ->map(function (Treatment $treatment) {
                return [
                    'paciente' => $treatment->patient?->nombre ?? $treatment->paciente,
                    'tratamiento' => $treatment->tratamiento,
                    'tipo' => $treatment->tipo,
                    'estado' => $treatment->estado,
                ];
            });

        $resumen = [
            'citas_hoy' => $agendaHoy->count(),
            'pacientes' => $isDoctor
                ? Patient::whereHas('appointments', fn ($query) => $query->where('dentist_id', $doctorDentist?->id ?? 0))->count()
                : Patient::count(),
            'dentistas' => Dentist::count(),
            'tratamientos_pendientes' => $this->scopeTreatmentsForRole(Treatment::where('estado', 'Pendiente'), $isDoctor, $doctorDentist)->count(),
        ];

        $monthlyRealizedTreatments = $this->scopeTreatmentsForRole(
            Treatment::with('patient')
                ->where('estado', 'Realizado')
                ->whereYear('fecha', now()->year)
                ->whereMonth('fecha', now()->month),
            $isDoctor,
            $doctorDentist
        )
            ->orderByDesc('fecha')
            ->get();

        $resumen['tratamientos_realizados_mes'] = $monthlyRealizedTreatments->count();

        $actividad = [
            'Se actualizaron ' . $this->scopeAppointmentsForRole(Appointment::where('estado', 'Confirmada'), $isDoctor, $doctorDentist)->count() . ' citas confirmadas.',
            'Hay ' . $this->scopeAppointmentsForRole(Appointment::where('estado', 'Pendiente'), $isDoctor, $doctorDentist)->count() . ' citas pendientes de confirmación.',
        ];

        $calendarAppointments = $this->scopeAppointmentsForRole(Appointment::with(['patient', 'dentist']), $isDoctor, $doctorDentist)
            ->whereYear('date', $selectedDate->year)
            ->whereMonth('date', $selectedDate->month)
            ->get()
            ->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment))
            ->values()
            ->toArray();

        $calendarData = $this->buildCalendar($selectedDate->toDateString(), $calendarAppointments);

        $selectedDayEvents = $this->scopeAppointmentsForRole(Appointment::with(['patient', 'dentist']), $isDoctor, $doctorDentist)
            ->whereDate('date', $selectedDate->toDateString())
            ->orderBy('time')
            ->get()
            ->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment));

        $selectedWeekStart = $selectedDate->copy()->startOfWeek();
        $selectedWeekEnd = $selectedWeekStart->copy()->endOfWeek();
        $selectedWeekAppointments = $this->scopeAppointmentsForRole(Appointment::with(['patient', 'dentist']), $isDoctor, $doctorDentist)
            ->whereBetween('date', [$selectedWeekStart->toDateString(), $selectedWeekEnd->toDateString()])
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->map(fn (Appointment $appointment) => $this->serializeAppointment($appointment));

        $weekDays = collect(range(0, 6))->map(function (int $offset) use ($selectedWeekStart, $selectedWeekAppointments) {
            $date = $selectedWeekStart->copy()->addDays($offset);

            return [
                'date' => $date,
                'events' => $selectedWeekAppointments
                    ->where('fecha', $date->toDateString())
                    ->values(),
            ];
        });

        $inventoryAlerts = InventoryItem::query()
            ->whereColumn('stock', '<=', 'reposicion')
            ->orderBy('stock')
            ->limit(4)
            ->get();

        $weekStart = now()->startOfWeek();
        $weeklyAppointments = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $isDoctor, $doctorDentist) {
            $date = $weekStart->copy()->addDays($offset);

            return [
                'label' => ucfirst($date->locale('es')->isoFormat('dd')),
                'date' => $date->format('d'),
                'count' => $this->scopeAppointmentsForRole(Appointment::whereDate('date', $date->toDateString()), $isDoctor, $doctorDentist)
                    ->where('estado', '!=', 'Cancelada')
                    ->count(),
                'today' => $date->isToday(),
            ];
        });

        $maxWeeklyAppointments = max(1, (int) $weeklyAppointments->max('count'));
        $appointmentStatus = collect(['Confirmada', 'Pendiente', 'Asistida', 'Terminada', 'Cancelada'])
            ->mapWithKeys(fn (string $status) => [
                $status => $this->scopeAppointmentsForRole(Appointment::where('estado', $status), $isDoctor, $doctorDentist)->count(),
            ]);
        $totalAppointments = max(1, (int) $appointmentStatus->sum());

        return view('dashboard.index', array_merge($calendarData, [
            'resumen' => $resumen,
            'agendaHoy' => $agendaHoy,
            'proximasCitas' => $proximasCitas,
            'tratamientos' => $tratamientos,
            'actividad' => $actividad,
            'inventoryAlerts' => $inventoryAlerts,
            'weeklyAppointments' => $weeklyAppointments,
            'maxWeeklyAppointments' => $maxWeeklyAppointments,
            'appointmentStatus' => $appointmentStatus,
            'totalAppointments' => $totalAppointments,
            'view' => $view,
            'selectedDate' => $selectedDate,
            'selectedDayEvents' => $selectedDayEvents,
            'weekDays' => $weekDays,
        ]));
    }

    public function calendar(Request $request)
    {
        $view = in_array($request->query('view', 'mes'), ['dia', 'semana', 'mes']) ? $request->query('view', 'mes') : 'mes';
        $year = max(1970, min(2100, (int) $request->query('year', now()->year)));
        $month = max(1, min(12, (int) $request->query('month', now()->month)));
        $selectedDate = now()->setDate($year, $month, 1);

        $appointments = Appointment::with(['patient', 'dentist'])->get();
        $serializedAppointments = $appointments
            ->map(fn(Appointment $appointment) => $this->serializeAppointment($appointment))
            ->values();
        $calendarData = $this->buildCalendar($selectedDate->toDateString(), $serializedAppointments->toArray());

        $today = now();
        $todayDate = $today->toDateString();
        $todayEvents = $appointments->filter(fn($appointment) => $appointment->date?->toDateString() === $todayDate)->map(fn($appointment) => $this->serializeAppointment($appointment))->values();

        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $weekStart->copy()->addDays(6);
        $weekEvents = $appointments->filter(function ($appointment) use ($weekStart, $weekEnd) {
            $date = $appointment->date?->toDateString();
            return $date >= $weekStart->toDateString() && $date <= $weekEnd->toDateString();
        })->map(fn($appointment) => $this->serializeAppointment($appointment))->values();

        return view('citas.calendario', array_merge($calendarData, [
            'citas' => $serializedAppointments->toArray(),
            'view' => $view,
            'today' => $today,
            'todayEvents' => $todayEvents,
            'weekEvents' => $weekEvents,
        ]));
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

    private function latestSystemUpdate(): string
    {
        $latest = collect([
            Appointment::max('updated_at'),
            Patient::max('updated_at'),
            Dentist::max('updated_at'),
            Treatment::max('updated_at'),
            InventoryItem::max('updated_at'),
            User::max('updated_at'),
            ClinicSetting::max('updated_at'),
        ])->filter()->max();

        return $latest
            ? 'Ultima actualizacion: '.date('d/m/Y H:i', strtotime($latest))
            : 'Sin actividad registrada';
    }

    private function serializeAppointment(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'paciente' => $appointment->patient?->nombre ?? $appointment->paciente,
            'dentista' => $appointment->dentist?->nombre ?? $appointment->dentista,
            'fecha' => $appointment->date?->toDateString() ?? $appointment->date,
            'hora' => $appointment->time,
            'tipo' => $appointment->tipo,
            'estado' => $appointment->estado,
            'notas' => $appointment->notas,
        ];
    }

    private function buildCalendar(string $referenceDate, array $appointments = []): array
    {
        $today = new \DateTimeImmutable($referenceDate);
        $monthStart = new \DateTimeImmutable($today->format('Y-m-01'));
        $daysInMonth = (int) $monthStart->format('t');
        $startWeekday = (int) $monthStart->format('N');

        $calendarDays = [];
        for ($slot = 1; $slot <= $startWeekday - 1 + $daysInMonth; $slot++) {
            if ($slot < $startWeekday) {
                $calendarDays[] = ['day' => null, 'date' => null, 'events' => []];
                continue;
            }

            $dayNumber = $slot - $startWeekday + 1;
            $date = $monthStart->modify('+' . ($dayNumber - 1) . ' days');
            $dateKey = $date->format('Y-m-d');

            $events = array_values(array_filter($appointments, function ($item) use ($dateKey) {
                $eventDate = $item['fecha'] ?? $item['date'] ?? null;

                return $eventDate && date('Y-m-d', strtotime($eventDate)) === $dateKey;
            }));

            $calendarDays[] = [
                'day' => $dayNumber,
                'date' => $dateKey,
                'events' => $events,
            ];
        }

        while (count($calendarDays) % 7 !== 0) {
            $calendarDays[] = ['day' => null, 'date' => null, 'events' => []];
        }

        $spanishMonths = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        return [
            'calendarDays' => $calendarDays,
            'monthName' => $spanishMonths[(int) $monthStart->format('n') - 1],
            'todayDate' => $referenceDate,
            'month' => (int) $monthStart->format('n'),
            'year' => (int) $monthStart->format('Y'),
            'prevMonth' => $monthStart->modify('-1 month'),
            'nextMonth' => $monthStart->modify('+1 month'),
        ];
    }
}
