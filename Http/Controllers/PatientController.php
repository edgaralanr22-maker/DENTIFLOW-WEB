<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $estado = $request->query('estado', 'Todos');
        $order = $request->query('order', 'visita_desc');

        $query = Patient::query();

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nombre', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('estado', 'like', "%{$search}%");
            });
        }

        if ($estado !== 'Todos' && $estado !== '') {
            if ($estado === 'Inactivo') {
                $query->whereIn('estado', ['Inactivo', 'Cancelada']);
            } else {
                $query->where('estado', $estado);
            }
        }

        $query = match ($order) {
            'visita_asc' => $query->orderBy('ultima_visita', 'asc'),
            'nombre_asc' => $query->orderBy('nombre', 'asc'),
            'nombre_desc' => $query->orderBy('nombre', 'desc'),
            default => $query->orderBy('ultima_visita', 'desc'),
        };

        $pacientes = $query->paginate(12)->withQueryString();

        $resumen = [
            'total' => Patient::count(),
            'activos' => Patient::where('estado', 'Activo')->count(),
            'pendientes' => Patient::where('estado', 'Pendiente')->count(),
            'inactivos' => Patient::whereIn('estado', ['Inactivo', 'Cancelada'])->count(),
        ];

        $estados = Patient::select('estado')->distinct()->orderBy('estado')->pluck('estado');

        return view('pacientes.index', compact('pacientes', 'resumen', 'estados'));
    }

    public function create()
    {
        return view('pacientes.crear');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:patients,email',
            'direccion' => 'nullable|string|max:500',
            'ultima_visita' => 'nullable|date',
            'estado' => 'required|string|max:50',
            'seguro' => 'nullable|string|max:150',
            'poliza' => 'nullable|string|max:100',
            'contacto_emergencia.nombre' => 'nullable|string|max:255',
            'contacto_emergencia.telefono' => 'nullable|string|max:20',
            'contacto_emergencia.relacion' => 'nullable|string|max:100',
        ], $this->validationMessages());

        $data = $this->normalizePatientData($data);

        Patient::create($data);

        return redirect()->route('pacientes')->with('success', 'Paciente agregado correctamente.');
    }

    public function edit($paciente)
    {
        $patient = $this->resolvePatient($paciente);

        return view('pacientes.editar', ['paciente' => [
            'id' => $patient->id,
            'nombre' => $patient->nombre,
            'telefono' => $patient->telefono,
            'email' => $patient->email,
            'direccion' => $patient->direccion,
            'seguro' => $patient->seguro,
            'poliza' => $patient->poliza,
            'contacto_emergencia' => $patient->contacto_emergencia ?? [],
            'estado' => $patient->estado,
            'ultima_visita' => $patient->ultima_visita?->format('d/m/Y'),
            'ultima_visita_input' => $patient->ultima_visita?->format('Y-m-d'),
            'key' => $patient->id,
        ]]);
    }

    public function update(Request $request, $paciente)
    {
        $patient = $this->resolvePatient($paciente);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('patients', 'email')->ignore($patient->id)],
            'direccion' => 'nullable|string|max:500',
            'ultima_visita' => 'nullable|date',
            'estado' => 'required|string|max:50',
            'seguro' => 'nullable|string|max:150',
            'poliza' => 'nullable|string|max:100',
            'contacto_emergencia.nombre' => 'nullable|string|max:255',
            'contacto_emergencia.telefono' => 'nullable|string|max:20',
            'contacto_emergencia.relacion' => 'nullable|string|max:100',
        ], $this->validationMessages());

        $data = $this->normalizePatientData($data);

        $patient->update($data);

        return redirect()->route('pacientes')->with('success', 'Paciente actualizado correctamente.');
    }

    public function destroy($paciente)
    {
        $patient = $this->resolvePatient($paciente);
        $patient->delete();

        return redirect()->route('pacientes')->with('success', "Paciente {$patient->nombre} eliminado correctamente.");
    }

    public function cancel($paciente)
    {
        $patient = $this->resolvePatient($paciente);
        $patient->update(['estado' => 'Cancelada']);

        return redirect()->route('pacientes')->with('success', "El estado de {$patient->nombre} se actualizó a Cancelada.");
    }

    public function show($paciente)
    {
        $patient = $this->resolvePatient($paciente);
        $patient->load([
            'medicalHistory',
            'clinicalRecords' => fn ($query) => $query->with('dentist')->orderByDesc('recorded_at'),
            'odontogramEntries',
            'appointments' => fn ($query) => $query->with('dentist')->orderByDesc('date')->orderByDesc('time'),
        ]);

        $nextAppointment = $patient->appointments()
            ->with('dentist')
            ->whereDate('date', '>=', now()->toDateString())
            ->whereNotIn('estado', ['Cancelada', 'Asistida', 'Terminada'])
            ->orderBy('date')
            ->orderBy('time')
            ->first();

        return view('pacientes.expediente', compact('patient', 'nextAppointment'));
    }

    public function history()
    {
        $tratamientos = Treatment::with('patient')
            ->orderByDesc('fecha')
            ->get()
            ->map(function ($treatment) {
                return [
                    'paciente' => $treatment->patient?->nombre ?? $treatment->paciente,
                    'tratamiento' => $treatment->tratamiento,
                    'fecha' => $treatment->fecha?->format('d/m/Y'),
                    'estado' => $treatment->estado,
                    'costo' => $treatment->costo,
                ];
            });

        $totalPacientes = Patient::count();
        $totalDinero = $tratamientos->sum('costo');
        $tratamientosCount = $tratamientos->where('costo', '>', 0)->groupBy('tratamiento')->map->count();

        return view('pacientes.historial', [
            'historial' => $tratamientos,
            'totalPacientes' => $totalPacientes,
            'totalDinero' => $totalDinero,
            'tratamientos' => $tratamientosCount,
        ]);
    }

    private function resolvePatient($value): Patient
    {
        if (is_numeric($value)) {
            return Patient::findOrFail($value);
        }

        return Patient::where('nombre', $value)->firstOrFail();
    }

    private function normalizePatientData(array $data): array
    {
        $data['email'] = trim((string) ($data['email'] ?? '')) ?: null;
        $data['ultima_visita'] = $data['ultima_visita'] ?? null;

        return $data;
    }

    private function validationMessages(): array
    {
        return [
            'email.unique' => 'Ese correo ya esta registrado en otro paciente.',
            'email.email' => 'Escribe un correo valido.',
            'ultima_visita.date' => 'La ultima visita debe ser una fecha valida.',
        ];
    }
}
