<?php

namespace App\Http\Controllers;

use App\Models\Dentist;
use App\Models\MedicalHistory;
use App\Models\OdontogramEntry;
use App\Models\Patient;
use Illuminate\Http\Request;

class ClinicalRecordController extends Controller
{
    public function store(Request $request, Patient $patient)
    {
        $this->authorizeDoctor();
        $data = $request->validate([
            'recorded_at' => 'required|date',
            'reason' => 'required|string|max:255',
            'diagnosis' => 'nullable|string|max:3000',
            'procedure' => 'nullable|string|max:3000',
            'notes' => 'nullable|string|max:5000',
            'prescription' => 'nullable|string|max:3000',
            'next_visit' => 'nullable|date',
        ]);

        $patient->clinicalRecords()->create($data + ['dentist_id' => $this->dentist()?->id]);

        return redirect()->route('pacientes.expediente', ['paciente' => $patient, 'tab' => 'consultas'])
            ->with('success', 'Consulta clínica registrada.');
    }

    public function updateMedicalHistory(Request $request, Patient $patient)
    {
        $this->authorizeDoctor();
        $data = $request->validate([
            'blood_type' => 'nullable|string|max:10',
            'allergies' => 'nullable|string|max:2000',
            'conditions' => 'nullable|string|max:2000',
            'medications' => 'nullable|string|max:2000',
            'surgeries' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:3000',
        ]);

        MedicalHistory::updateOrCreate(['patient_id' => $patient->id], $data);

        return redirect()->route('pacientes.expediente', ['paciente' => $patient, 'tab' => 'antecedentes'])
            ->with('success', 'Antecedentes médicos actualizados.');
    }

    public function updateTooth(Request $request, Patient $patient)
    {
        $this->authorizeDoctor();
        $data = $request->validate([
            'tooth_number' => 'required|integer|min:1|max:32',
            'status' => 'required|in:Sano,Caries,Restaurado,Ausente,Corona,Implante,Endodoncia',
            'notes' => 'nullable|string|max:1000',
        ]);

        OdontogramEntry::updateOrCreate(
            ['patient_id' => $patient->id, 'tooth_number' => $data['tooth_number']],
            ['dentist_id' => $this->dentist()?->id, 'status' => $data['status'], 'notes' => $data['notes'] ?? null]
        );

        return redirect()->route('pacientes.expediente', ['paciente' => $patient, 'tab' => 'odontograma'])
            ->with('success', 'Odontograma actualizado.');
    }

    private function dentist(): ?Dentist
    {
        // La nota clinica debe quedar ligada al doctor autenticado, no a un nombre que puede repetirse o cambiar.
        return Dentist::where('user_id', session('access_user_id'))->first();
    }

    private function authorizeDoctor(): void
    {
        abort_unless(session('access_role') === 'doctor', 403);
    }
}
