<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    private const VALID_STATUSES = ['Pendiente', 'Confirmada', 'Cancelada', 'Asistida', 'Terminada'];

    public function index(Request $request)
    {
        $currentDentist = $this->currentDoctorDentist();
        $search = trim(strtolower($request->query('search', '')));
        $estado = trim($request->query('estado', 'Todos'));
        $dentista = trim($request->query('dentista', 'Todos'));
        $fecha = trim($request->query('fecha', ''));
        $order = $request->query('order', 'fecha_asc');

        $query = Appointment::with(['patient', 'dentist']);

        if ($currentDentist) {
            $query->where('dentist_id', $currentDentist->id);
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->whereHas('patient', fn($q) => $q->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('dentist', fn($q) => $q->where('nombre', 'like', "%{$search}%"))
                    ->orWhere('tipo', 'like', "%{$search}%");
            });
        }

        if ($estado !== '' && $estado !== 'Todos') {
            $query->where('estado', $estado);
        }

        if ($dentista !== '' && $dentista !== 'Todos') {
            $query->whereHas('dentist', fn($q) => $q->where('nombre', $dentista));
        }

        if ($fecha !== '') {
            $query->whereDate('date', $fecha);
        }

        $query = match ($order) {
            'fecha_desc' => $query->orderByDesc('date')->orderByDesc('time'),
            'paciente_asc' => $query->join('patients', 'appointments.patient_id', '=', 'patients.id')->orderBy('patients.nombre')->select('appointments.*'),
            'dentista_asc' => $query->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')->orderBy('dentists.nombre')->select('appointments.*'),
            default => $query->orderBy('date')->orderBy('time'),
        };

        $appointments = $query->get()->map(function (Appointment $appointment) {
            return $this->serializeAppointment($appointment);
        });

        $filtered = $appointments;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 6;
        $currentItems = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values()->all();
        $citasPaginator = new LengthAwarePaginator($currentItems, $filtered->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $baseCitas = Appointment::query()
            ->when($currentDentist, fn ($query) => $query->where('dentist_id', $currentDentist->id))
            ->get();
        $today = now()->toDateString();

        $resumen = [
            'hoy' => $baseCitas->where('date', $today)->count(),
            'confirmadas' => $baseCitas->where('estado', 'Confirmada')->count(),
            'pendientes' => $baseCitas->where('estado', 'Pendiente')->count(),
            'proximas' => $baseCitas->where('date', '>=', $today)->whereNotIn('estado', ['Cancelada', 'Asistida', 'Terminada'])->count(),
        ];

        $estados = Appointment::select('estado')->distinct()->pluck('estado')->sort()->values();
        $dentistas = Dentist::query()
            ->when($currentDentist, fn ($query) => $query->whereKey($currentDentist->id))
            ->select('nombre')
            ->distinct()
            ->pluck('nombre')
            ->sort()
            ->values();

        return view('citas.index', [
            'citas' => $citasPaginator,
            'resumen' => $resumen,
            'estados' => $estados,
            'dentistas' => $dentistas,
        ]);
    }

    public function create()
    {
        $pacientes = Patient::orderBy('nombre')->pluck('nombre');
        $dentistas = Dentist::orderBy('nombre')->pluck('nombre');
        $tratamientos = Treatment::orderBy('tratamiento')
            ->whereNull('appointment_id')
            ->whereNull('patient_id')
            ->get(['tratamiento', 'tipo', 'costo'])
            ->unique('tratamiento')
            ->values();
        $currentDentist = $this->currentDoctorDentist();

        return view('citas.crear', compact('pacientes', 'dentistas', 'tratamientos', 'currentDentist'));
    }

    public function store(Request $request)
    {
        $currentDentist = $this->currentDoctorDentist();

        $data = $request->validate([
            'paciente' => 'required|string|exists:patients,nombre',
            'dentista' => $currentDentist ? 'nullable' : 'required|string|exists:dentists,nombre',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'tipo' => 'required|string|exists:treatments,tratamiento',
        ]);

        $patient = $this->resolvePatient($data['paciente']);
        $dentist = $currentDentist ?? $this->resolveDentist($data['dentista']);
        $time = date('H:i:s', strtotime($data['hora']));

        if ($availabilityError = $this->availabilityError($dentist, $data['fecha'], $time)) {
            return back()->withErrors(['hora' => $availabilityError])->withInput();
        }

        if ($conflictingAppointment = $this->findConflictingAppointment($dentist, $data['fecha'], $time)) {
            return back()
                ->withErrors(['hora' => $this->conflictMessage($conflictingAppointment)])
                ->withInput();
        }

        Appointment::create([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => $data['fecha'],
            'time' => $time,
            'tipo' => $data['tipo'],
            'estado' => ClinicSetting::current()->default_appointment_status,
        ]);

        return redirect()->route('citas')->with('success', 'Cita creada correctamente.');
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorizeAppointmentAccess($appointment);
        $pacientes = Patient::orderBy('nombre')->pluck('nombre');
        $dentistas = Dentist::orderBy('nombre')->pluck('nombre');

        return view('citas.editar', [
            'cita' => $this->serializeAppointment($appointment),
            'pacientes' => $pacientes,
            'dentistas' => $dentistas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorizeAppointmentAccess($appointment);

        $data = $request->validate([
            'paciente' => 'required|string|exists:patients,nombre',
            'dentista' => 'required|string|exists:dentists,nombre',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'tipo' => 'required|string|exists:treatments,tratamiento',
            'estado' => 'required|string|in:'.implode(',', self::VALID_STATUSES),
        ]);

        $patient = $this->resolvePatient($data['paciente']);
        $dentist = $this->resolveDentist($data['dentista']);
        $time = date('H:i:s', strtotime($data['hora']));

        if ($availabilityError = $this->availabilityError($dentist, $data['fecha'], $time)) {
            return back()->withErrors(['hora' => $availabilityError])->withInput();
        }

        if ($conflictingAppointment = $this->findConflictingAppointment($dentist, $data['fecha'], $time, $appointment->id)) {
            return back()
                ->withErrors(['hora' => $this->conflictMessage($conflictingAppointment)])
                ->withInput();
        }

        $appointment->update([
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'date' => $data['fecha'],
            'time' => $time,
            'tipo' => $data['tipo'],
            'estado' => $data['estado'],
        ]);

        return redirect()->route('citas')->with('success', 'Cita actualizada correctamente.');
    }

    public function confirm($id)
    {
        return $this->updateEstado($id, 'Confirmada', 'Cita #%s confirmada.');
    }

    public function cancel($id)
    {
        return $this->updateEstado($id, 'Cancelada', 'Cita #%s cancelada.');
    }

    public function attended($id)
    {
        return $this->updateEstado($id, 'Asistida', 'Cita #%s marcada como asistida.');
    }

    public function finish($id)
    {
        $appointment = Appointment::with('patient')->findOrFail($id);

        if (session('access_role') === 'doctor') {
            abort_unless($appointment->dentist_id === $this->currentDoctorDentist()?->id, 403);
        }

        if ($appointment->estado === 'Cancelada') {
            return redirect()->route('citas')->withErrors([
                'estado' => 'Una cita cancelada no se puede marcar como terminada.',
            ]);
        }

        $catalogTreatment = Treatment::query()
            ->whereNull('appointment_id')
            ->whereNull('patient_id')
            ->where('tratamiento', $appointment->tipo)
            ->first();

        if (! $catalogTreatment) {
            return redirect()->route('citas')->withErrors([
                'tratamiento' => 'No se encontro el tratamiento de la cita en el catalogo.',
            ]);
        }

        DB::transaction(function () use ($appointment, $catalogTreatment) {
            Treatment::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id' => $appointment->patient_id,
                    'paciente' => $appointment->patient?->nombre ?? '',
                    'tratamiento' => $catalogTreatment->tratamiento,
                    'tipo' => $catalogTreatment->tipo,
                    'fecha' => now()->toDateString(),
                    'estado' => 'Realizado',
                    'costo' => $catalogTreatment->costo,
                    'descripcion' => $catalogTreatment->descripcion,
                ]
            );

            $appointment->update(['estado' => 'Terminada']);
        });

        return redirect()->route('citas')->with('success', "Cita #{$appointment->id} terminada. La ganancia ya se refleja en Reportes.");
    }

    public function reprogram($id)
    {
        $this->authorizeAppointmentAccess(Appointment::findOrFail($id));

        // Reprogramar reutiliza la edicion existente para conservar el mismo formulario del front.
        return redirect()->route('citas.edit', ['id' => $id]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if ($appointment) {
            $this->authorizeAppointmentAccess($appointment);
            $appointment->delete();
            return redirect()->route('citas')->with('success', "Cita #{$id} eliminada correctamente.");
        }

        return redirect()->route('citas')->with('success', "Cita #{$id} no encontrada.");
    }

    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorizeAppointmentAccess($appointment);

        return view('citas.detalle', ['cita' => $this->serializeAppointment($appointment)]);
    }

    private function updateEstado($id, string $estado, string $message)
    {
        abort_unless(in_array($estado, self::VALID_STATUSES, true), 422);

        $appointment = Appointment::find($id);

        if (! $appointment) {
            return redirect()->route('citas')->with('success', sprintf("Cita #%s no encontrada.", $id));
        }

        $this->authorizeAppointmentAccess($appointment);
        $appointment->update(['estado' => $estado]);

        return redirect()->route('citas')->with('success', sprintf($message, $id));
    }

    private function serializeAppointment(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'paciente' => $appointment->patient?->nombre ?? $appointment->paciente,
            'dentista' => $appointment->dentist?->nombre ?? $appointment->dentista,
            'fecha' => $appointment->date?->toDateString() ?? $appointment->date,
            'hora' => date('h:i A', strtotime($appointment->time)),
            'tipo' => $appointment->tipo,
            'estado' => $appointment->estado,
        ];
    }

    private function authorizeAppointmentAccess(Appointment $appointment): void
    {
        if (session('access_role') !== 'doctor') {
            return;
        }

        abort_unless($appointment->dentist_id === $this->currentDoctorDentist()?->id, 403);
    }

    private function resolvePatient(string $name): Patient
    {
        return Patient::where('nombre', $name)->firstOrFail();
    }

    private function resolveDentist(string $name): Dentist
    {
        return Dentist::where('nombre', $name)->firstOrFail();
    }

    private function currentDoctorDentist(): ?Dentist
    {
        if (session('access_role') !== 'doctor') {
            return null;
        }

        $user = User::find(session('access_user_id'))
            ?? User::where('email', session('access_email'))->first();

        if (! $user && session('access_email')) {
            $user = User::create([
                'name' => session('access_name') ?: Str::before(session('access_email'), '@'),
                'email' => session('access_email'),
                'role' => 'doctor',
                'password' => Hash::make(Str::random(32)),
            ]);

            session([
                'access_user_id' => $user->id,
                'access_name' => $user->name,
            ]);
        }

        abort_unless($user, 403, 'La sesion activa no esta vinculada a un usuario dentista.');

        $dentist = Dentist::where('user_id', $user->id)->first()
            ?? Dentist::whereNull('user_id')->where('nombre', $user->name)->first();

        if ($dentist) {
            if (! $dentist->user_id) {
                $dentist->update(['user_id' => $user->id]);
            }

            return $dentist;
        }

        return Dentist::create([
            'user_id' => $user->id,
            'nombre' => $user->name,
            'especialidad' => 'Odontologia general',
            'telefono' => 'No registrado',
        ]);
    }

    private function findConflictingAppointment(Dentist $dentist, string $date, string $time, ?int $ignoreAppointmentId = null): ?Appointment
    {
        $duration = $this->appointmentDuration();
        $requestedStart = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$time}");
        $requestedEnd = $requestedStart->copy()->addMinutes($duration);

        $appointments = Appointment::query()
            ->where('dentist_id', $dentist->id)
            ->where('estado', '!=', 'Cancelada')
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->orderBy('time')
            ->get();

        return $appointments->first(function (Appointment $appointment) use ($date, $duration, $requestedStart, $requestedEnd) {
            if ($appointment->date->toDateString() !== $date) {
                return false;
            }

            $existingStart = Carbon::parse($appointment->date->toDateString().' '.$appointment->time);
            $existingEnd = $existingStart->copy()->addMinutes($duration);

            return $requestedStart->lt($existingEnd) && $requestedEnd->gt($existingStart);
        });
    }

    private function availabilityError(Dentist $dentist, string $date, string $time): ?string
    {
        $duration = $this->appointmentDuration();
        $requestedStart = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$time}");
        $requestedEnd = $requestedStart->copy()->addMinutes($duration);

        if ($requestedStart->isPast()) {
            return 'No se pueden agendar citas en fechas u horas pasadas.';
        }

        $weekday = (int) $requestedStart->isoWeekday();
        $schedule = DentistSchedule::where('dentist_id', $dentist->id)->where('weekday', $weekday)->first();

        if (! $schedule) {
            return null;
        }

        if (! $schedule->enabled) {
            return 'El dentista no tiene agenda activa para ese dia.';
        }

        $workStart = Carbon::parse($date.' '.$schedule->start_time);
        $workEnd = Carbon::parse($date.' '.$schedule->end_time);

        if ($requestedStart->lt($workStart) || $requestedEnd->gt($workEnd)) {
            return sprintf(
                'La cita debe estar dentro del horario del dentista: %s a %s.',
                $workStart->format('H:i'),
                $workEnd->format('H:i')
            );
        }

        return null;
    }

    private function conflictMessage(Appointment $appointment): string
    {
        $duration = $this->appointmentDuration();
        $start = Carbon::parse($appointment->date->toDateString().' '.$appointment->time);
        $end = $start->copy()->addMinutes($duration);

        return sprintf(
            'El dentista ya tiene una cita de %s a %s. Elige un horario fuera de ese bloque de %d minutos.',
            $start->format('H:i'),
            $end->format('H:i'),
            $duration
        );
    }

    private function appointmentDuration(): int
    {
        return ClinicSetting::current()->appointment_duration;
    }
}
