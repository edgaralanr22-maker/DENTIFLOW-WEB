<?php

namespace App\Http\Controllers;

use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DentistController extends Controller
{
    public function index()
    {
        $dentistas = Dentist::with('user')->orderBy('nombre')->get();

        return view('dentists.index', compact('dentistas'));
    }

    public function create()
    {
        return view('dentists.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:dentists,nombre',
            'especialidad' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['nombre'],
            'email' => $data['email'],
            'role' => 'doctor',
            'password' => $data['password'],
            'telefono' => $data['telefono'],
            'puesto' => $data['especialidad'] ?? null,
        ]);

        $dentist = Dentist::create([
            'user_id' => $user->id,
            'nombre' => $data['nombre'],
            'especialidad' => $data['especialidad'] ?? null,
            'telefono' => $data['telefono'],
        ]);
        $this->ensureDefaultSchedule($dentist);

        return redirect()->route('dentistas')->with('success', 'Dentista agregado correctamente. Ya puede iniciar sesion como doctor con su correo.');
    }

    public function edit($id)
    {
        $dentista = Dentist::findOrFail($id);

        return view('dentists.edit', compact('dentista'));
    }

    public function update(Request $request, $id)
    {
        $dentista = Dentist::findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('dentists', 'nombre')->ignore($dentista->id)],
            'especialidad' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:50',
            'email' => [
                $dentista->user ? 'nullable' : 'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($dentista->user_id),
            ],
            'password' => [$dentista->user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        $dentista->update([
            'nombre' => $data['nombre'],
            'especialidad' => $data['especialidad'] ?? null,
            'telefono' => $data['telefono'],
        ]);

        if ($dentista->user) {
            $userData = [
                'name' => $data['nombre'],
                'email' => $data['email'] ?: $dentista->user->email,
                'telefono' => $data['telefono'],
                'puesto' => $data['especialidad'] ?? null,
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $dentista->user->update($userData);
        } else {
            $user = User::create([
                'name' => $data['nombre'],
                'email' => $data['email'],
                'role' => 'doctor',
                'password' => $data['password'],
                'telefono' => $data['telefono'],
                'puesto' => $data['especialidad'] ?? null,
            ]);

            $dentista->update(['user_id' => $user->id]);
        }

        return redirect()->route('dentistas')->with('success', 'Dentista actualizado correctamente.');
    }

    public function destroy($id)
    {
        $dentista = Dentist::find($id);

        if ($dentista) {
            if ($dentista->appointments()->exists()) {
                return redirect()->route('dentistas')->withErrors(['dentista' => 'No puedes eliminar un dentista con citas registradas.']);
            }

            $dentista->delete();
        }

        return redirect()->route('dentistas')->with('success', 'Dentista eliminado.');
    }

    private function ensureDefaultSchedule(Dentist $dentist): void
    {
        foreach (range(1, 7) as $weekday) {
            // Cada dentista nuevo recibe una agenda base para que citas pueda validar disponibilidad.
            DentistSchedule::firstOrCreate(
                ['dentist_id' => $dentist->id, 'weekday' => $weekday],
                [
                    'enabled' => $weekday <= 5,
                    'start_time' => '09:00',
                    'end_time' => $weekday === 5 ? '15:00' : '17:00',
                ]
            );
        }
    }
}
