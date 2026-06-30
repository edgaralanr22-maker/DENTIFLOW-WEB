<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $usuario = $this->profileUser();
        $lastAccess = $usuario->updated_at?->diffForHumans() ?? 'Sin actividad registrada';

        if (session('access_role') === 'admin') {
            return view('admin.profile', compact('usuario'));
        }

        if (session('access_role') === 'asistente') {
            return view('perfil.asistente', [
                'usuario' => $this->assistantProfileData($usuario),
                'actividades' => $this->assistantActivities(),
                'lastAccess' => $lastAccess,
                'citasHoy' => Appointment::whereDate('date', now()->toDateString())->count(),
                'citasPendientes' => Appointment::where('estado', 'Pendiente')->count(),
                'pacientesRegistrados' => Patient::count(),
                'alertasInventario' => InventoryItem::whereColumn('stock', '<=', 'reposicion')->count(),
            ]);
        }

        $dentist = $this->profileDentist($usuario);

        return view('perfil.index', [
            'usuario' => $this->profileData($usuario, $dentist),
            'agenda' => $this->scheduleData($dentist),
            'actividades' => $this->doctorActivities($dentist),
            'lastAccess' => $lastAccess,
            'citasConfirmadas' => Appointment::where('dentist_id', $dentist->id)->where('estado', 'Confirmada')->count(),
            'pacientesActivos' => Patient::whereHas('appointments', fn ($query) => $query->where('dentist_id', $dentist->id))->count(),
        ]);
    }

    public function edit()
    {
        $usuario = $this->profileUser();

        if (session('access_role') === 'admin') {
            return view('admin.profile-edit', compact('usuario'));
        }

        if (session('access_role') === 'asistente') {
            return view('perfil.editar', [
                'usuario' => $this->assistantProfileData($usuario),
                'role' => 'asistente',
            ]);
        }

        return view('perfil.editar', [
            'usuario' => $this->profileData($usuario, $this->profileDentist($usuario)),
            'role' => 'doctor',
        ]);
    }

    public function update(Request $request)
    {
        $usuario = $this->profileUser();
        $dentist = session('access_role') === 'doctor' ? $this->profileDentist($usuario) : null;
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'telefono' => 'nullable|string|max:50',
            'especialidad' => 'nullable|string|max:100',
        ]);

        if ($dentist && Dentist::where('nombre', $data['nombre'])->whereKeyNot($dentist->id)->exists()) {
            return back()->withErrors(['nombre' => 'Ya existe un dentista con ese nombre.'])->withInput();
        }

        $usuario->update(['name' => $data['nombre'], 'email' => $data['email']]);
        session(['access_name' => $data['nombre'], 'access_email' => $data['email']]);

        if (session('access_role') === 'asistente') {
            $usuario->update([
                'telefono' => $data['telefono'] ?? null,
                'puesto' => $data['especialidad'] ?? null,
            ]);
        }

        if (session('access_role') === 'doctor') {
            // El doctor se actualiza por usuario para no depender de nombres que pueden repetirse.
            $dentist->update([
                'nombre' => $data['nombre'],
                'telefono' => $this->filledOrDefault($data['telefono'] ?? null, 'No registrado'),
                'especialidad' => $this->filledOrDefault($data['especialidad'] ?? null, 'Odontologia general'),
            ]);
        }

        return redirect()->route('perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    public function updateSchedule(Request $request)
    {
        abort_unless(session('access_role') === 'doctor', 403);

        $data = $request->validate([
            'schedule' => 'required|array|size:7',
            'schedule.*' => 'required|array',
            'schedule.*.enabled' => 'required|boolean',
            'schedule.*.start_time' => 'required|date_format:H:i',
            'schedule.*.end_time' => 'required|date_format:H:i',
        ]);

        $dentist = $this->profileDentist($this->profileUser());

        foreach ($data['schedule'] as $weekday => $schedule) {
            if ((int) $weekday < 1 || (int) $weekday > 7) {
                return back()->withErrors(['schedule' => 'La agenda solo permite dias de la semana del 1 al 7.']);
            }

            if ($schedule['enabled'] && $schedule['end_time'] <= $schedule['start_time']) {
                return back()->withErrors(['schedule' => 'La hora de salida debe ser posterior a la hora de entrada.']);
            }

            DentistSchedule::updateOrCreate(
                ['dentist_id' => $dentist->id, 'weekday' => (int) $weekday],
                [
                    'enabled' => (bool) $schedule['enabled'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                ]
            );
        }

        return redirect()->route('perfil')->with('success', 'Agenda semanal actualizada correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $usuario = $this->profileUser();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $usuario->password)) {
            return back()->withErrors(['current_password' => 'La contrasena actual no es correcta.']);
        }

        // El cast hashed del modelo User cifra la nueva contrasena antes de guardarla.
        $usuario->update(['password' => $data['password']]);

        return redirect()->route('perfil')->with('success', 'Contrasena actualizada correctamente.');
    }

    private function profileUser(): User
    {
        if (session('access_role') === 'admin') {
            return User::firstOrCreate(
                ['email' => 'admin@gmail.com'],
                ['name' => 'Administrador DentiFlow', 'role' => 'admin', 'password' => bcrypt('Admin123!')]
            );
        }

        if (session('access_role') === 'asistente') {
            return User::find(session('access_user_id'))
                ?? User::where('email', session('access_email'))->first()
                ?? User::firstOrCreate(
                    ['email' => 'asistente@dentiflow.com'],
                    [
                        'name' => 'Asistente DentiFlow',
                        'role' => 'asistente',
                        'password' => bcrypt('Asistente123!'),
                        'telefono' => 'No registrado',
                        'puesto' => 'Recepcion y gestion de citas',
                    ]
                );
        }

        return User::find(session('access_user_id'))
            ?? User::where('email', session('access_email'))->first()
            ?? User::firstOrCreate(
                ['email' => 'laura.ramirez@dentiflow.com'],
                ['name' => 'Dr. Samuel', 'role' => 'doctor', 'password' => bcrypt('password')]
            );
    }

    private function profileDentist(User $user): Dentist
    {
        // El perfil clinico del doctor debe seguir al usuario, no al nombre mostrado en pantalla.
        return Dentist::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nombre' => session('access_name') ?: $user->name,
                'especialidad' => 'Odontología general',
                'telefono' => 'No registrado',
            ]
        );
    }

    private function scheduleData(Dentist $dentist)
    {
        $days = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

        return collect($days)->map(function (string $label, int $weekday) use ($dentist) {
            $schedule = DentistSchedule::firstOrCreate(
                ['dentist_id' => $dentist->id, 'weekday' => $weekday],
                [
                    'enabled' => $weekday <= 5,
                    'start_time' => '09:00',
                    'end_time' => $weekday === 5 ? '15:00' : '17:00',
                ]
            );

            return [
                'weekday' => $weekday,
                'dia' => $label,
                'enabled' => $schedule->enabled,
                'start_time' => substr($schedule->start_time, 0, 5),
                'end_time' => substr($schedule->end_time, 0, 5),
                'horario' => $schedule->enabled
                    ? substr($schedule->start_time, 0, 5).' – '.substr($schedule->end_time, 0, 5)
                    : 'No disponible',
            ];
        })->values();
    }

    private function profileData(User $user, Dentist $dentist): array
    {
        return [
            'nombre' => $user->name,
            'email' => $user->email,
            'telefono' => $dentist->telefono ?: 'No registrado',
            'especialidad' => $dentist->especialidad ?: 'Odontología general',
        ];
    }

    private function assistantProfileData(User $user): array
    {
        return [
            'nombre' => $user->name,
            'email' => $user->email,
            'telefono' => $user->telefono ?: 'No registrado',
            'especialidad' => $user->puesto ?: 'Recepcion y gestion de citas',
        ];
    }

    private function doctorActivities(Dentist $dentist)
    {
        $appointments = Appointment::with('patient')
            ->where('dentist_id', $dentist->id)
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->map(fn (Appointment $appointment) => [
                'texto' => sprintf(
                    'Cita %s con %s',
                    strtolower($appointment->estado),
                    $appointment->patient?->nombre ?? 'paciente sin nombre'
                ),
                'tiempo' => $appointment->updated_at?->diffForHumans() ?? 'Sin fecha',
            ]);

        if ($appointments->isNotEmpty()) {
            return $appointments;
        }

        return collect([[
            'texto' => 'Sin actividad clinica reciente registrada',
            'tiempo' => 'Pendiente',
        ]]);
    }

    private function assistantActivities()
    {
        $appointments = Appointment::with('patient')
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->map(fn (Appointment $appointment) => [
                'texto' => sprintf(
                    'Cita %s para %s',
                    strtolower($appointment->estado),
                    $appointment->patient?->nombre ?? 'paciente sin nombre'
                ),
                'tiempo' => $appointment->updated_at?->diffForHumans() ?? 'Sin fecha',
            ]);

        if ($appointments->isNotEmpty()) {
            return $appointments;
        }

        return collect([[
            'texto' => 'Sin actividad operativa reciente registrada',
            'tiempo' => 'Pendiente',
        ]]);
    }

    private function filledOrDefault(?string $value, string $default): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : $default;
    }
}
