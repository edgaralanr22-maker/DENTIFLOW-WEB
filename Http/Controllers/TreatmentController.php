<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TreatmentController extends Controller
{
    private const CATALOG_STATUS = 'Activo';
    private const VALID_TYPES = ['Preventivo', 'Correctivo', 'Estético', 'Quirúrgico', 'Diagnóstico', 'Restaurativo'];

    public function create()
    {
        return view('pacientes.tratamiento', ['edit' => false, 'tratamiento' => []]);
    }

    public function store(Request $request)
    {
        Treatment::create($this->validatedData($request) + [
            'paciente' => '',
            'fecha' => now()->toDateString(),
            'estado' => self::CATALOG_STATUS,
        ]);

        return redirect()->route('pacientes.tratamientos')->with('success', 'Tratamiento agregado al catálogo.');
    }

    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $tipo = $request->query('tipo', 'Todos');
        $order = $request->query('order', 'nombre_asc');

        $query = Treatment::query()
            ->whereNull('appointment_id')
            ->whereNull('patient_id');

        if ($search !== '') {
            $query->where(fn ($subQuery) => $subQuery
                ->where('tratamiento', 'like', "%{$search}%")
                ->orWhere('tipo', 'like', "%{$search}%")
                ->orWhere('descripcion', 'like', "%{$search}%"));
        }

        if ($tipo !== 'Todos' && $tipo !== '') {
            $query->where('tipo', $tipo);
        }

        $query = match ($order) {
            'nombre_desc' => $query->orderByDesc('tratamiento'),
            'costo_desc' => $query->orderByDesc('costo'),
            'costo_asc' => $query->orderBy('costo'),
            default => $query->orderBy('tratamiento'),
        };

        $tratamientos = $query->get()->map(fn (Treatment $treatment) => [
            'id' => $treatment->id,
            'tratamiento' => $treatment->tratamiento,
            'tipo' => $treatment->tipo,
            'costo' => $treatment->costo,
            'descripcion' => $treatment->descripcion,
        ]);

        $allTreatments = Treatment::query()
            ->whereNull('appointment_id')
            ->whereNull('patient_id')
            ->get();
        $resumen = [
            'total' => $allTreatments->count(),
            'tipos' => $allTreatments->pluck('tipo')->filter()->unique()->count(),
            'promedio' => $allTreatments->avg('costo') ?? 0,
        ];
        $tipos = $allTreatments->pluck('tipo')->filter()->unique()->sort()->values();

        return view('pacientes.tratamientos.index', compact('tratamientos', 'resumen', 'tipos'));
    }

    public function edit($id)
    {
        $treatment = Treatment::findOrFail($id);

        return view('pacientes.tratamiento', [
            'edit' => true,
            'tratamiento' => $treatment->only(['id', 'tratamiento', 'tipo', 'costo', 'descripcion']),
        ]);
    }

    public function update(Request $request, $id)
    {
        Treatment::findOrFail($id)->update($this->validatedData($request, $id));

        return redirect()->route('pacientes.tratamientos')->with('success', 'Tratamiento actualizado correctamente.');
    }

    public function destroy($id)
    {
        $treatment = Treatment::findOrFail($id);

        if ($this->isTreatmentInUse($treatment)) {
            return redirect()->route('pacientes.tratamientos')->withErrors([
                'tratamiento' => 'No puedes eliminar un tratamiento usado en citas, pacientes o reportes.',
            ]);
        }

        $treatment->delete();

        return redirect()->route('pacientes.tratamientos')->with('success', 'Tratamiento eliminado del catálogo.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'tratamiento' => [
                'required',
                'string',
                'max:255',
                Rule::unique('treatments', 'tratamiento')
                    ->whereNull('appointment_id')
                    ->whereNull('patient_id')
                    ->ignore($ignoreId),
            ],
            'tipo' => ['required', 'string', 'max:100', Rule::in(self::VALID_TYPES)],
            'costo' => 'required|numeric|min:0',
            'descripcion' => 'nullable|string|max:2000',
        ]);
    }

    private function isTreatmentInUse(Treatment $treatment): bool
    {
        // El catalogo no se elimina si ya fue usado por una cita o se convirtio en registro clinico/financiero.
        return $treatment->patient_id !== null
            || $treatment->appointment_id !== null
            || $treatment->appointmentsUsingName()->exists();
    }
}
