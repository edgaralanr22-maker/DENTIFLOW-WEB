<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\ClinicalRecordController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Models\User;

Route::get('/', function () {
    return view('auth.role-selector');
})->name('role.select');

Route::get('/login', function (Request $request) {
    $roles = ['admin', 'doctor', 'asistente'];
    $role = in_array($request->query('role'), $roles, true) ? $request->query('role') : null;

    return $role
        ? view('auth.login', compact('role'))
        : redirect()->route('role.select');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'role' => 'required|in:admin,doctor,asistente',
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->input('email'))->first();

    if (! $user || ! Hash::check($request->input('password'), $user->password)) {
        return back()->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])->withInput($request->only('email'));
    }

    if ($user->role !== $request->input('role')) {
        return back()->withErrors(['role' => 'La cuenta no corresponde al tipo de acceso seleccionado.'])->withInput($request->only('email'));
    }

    // La sesion conserva solo los datos necesarios para el middleware y las vistas existentes.
    session([
        'access_role' => $user->role,
        'access_user_id' => $user->id,
        'access_name' => $user->name,
        'access_email' => $user->email,
    ]);

    $request->session()->regenerate();

    return redirect()->route('inicio');
});

Route::get('/register', function (Request $request) {
    $roles = ['admin', 'doctor', 'asistente'];
    $role = in_array($request->query('role'), $roles, true) ? $request->query('role') : 'doctor';

    return view('auth.register', compact('role'));
})->name('register');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'role' => 'required|in:admin,doctor,asistente',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // El registro publico crea la cuenta real; el rol queda persistido para validar accesos posteriores.
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'role' => $data['role'],
        'password' => $data['password'],
    ]);

    if ($data['role'] === 'doctor') {
        $user->dentist()->create([
            'nombre' => $user->name,
            'telefono' => 'No registrado',
            'especialidad' => 'Odontologia general',
        ]);
    }

    return redirect()->route('login', ['role' => $data['role']])->with('success', 'Cuenta creada correctamente. Ahora puedes iniciar sesion.');
});

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    // Laravel usa password_reset_tokens para generar y validar el token de recuperacion.
    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? back()->with('success', __($status))
        : back()->withErrors(['email' => __($status)]);
})->name('password.email');

Route::get('/reset-password/{token}', function (string $token, Request $request) {
    return response()->json([
        'token' => $token,
        'email' => $request->query('email'),
        'message' => 'Endpoint backend listo para restablecer contrasena mediante POST /reset-password.',
    ]);
})->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('success', __($status))
        : back()->withErrors(['email' => __($status)]);
})->name('password.update');

Route::post('/logout', function () {
    session()->forget(['access_role', 'access_user_id', 'access_name', 'access_email']);
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('role.select');
})->name('logout');

Route::get('/administracion/configuracion', [AdminSettingsController::class, 'edit'])->name('admin.settings');

Route::post('/administracion/configuracion', [AdminSettingsController::class, 'update'])->name('admin.settings.update');

Route::get('/administracion/usuarios', [AdminUserController::class, 'index'])->name('admin.users');
Route::get('/administracion/usuarios/{user}/editar', [AdminUserController::class, 'edit'])->name('admin.users.edit');
Route::put('/administracion/usuarios/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');

Route::redirect('/dashboard', '/inicio');
Route::get('/inicio', [DashboardController::class, 'index'])->name('inicio');
Route::get('/calendario', [DashboardController::class, 'calendar'])->name('calendario');

Route::get('/pacientes', [PatientController::class, 'index'])->name('pacientes');
Route::get('/pacientes/crear', [PatientController::class, 'create'])->name('pacientes.create');
Route::post('/pacientes', [PatientController::class, 'store'])->name('pacientes.store');
Route::get('/pacientes/{paciente}/editar', [PatientController::class, 'edit'])->name('pacientes.edit')->where('paciente', '[^/]+');
Route::put('/pacientes/{paciente}', [PatientController::class, 'update'])->name('pacientes.update')->where('paciente', '[^/]+');
Route::get('/pacientes/{paciente}/eliminar', [PatientController::class, 'destroy'])->name('pacientes.delete')->where('paciente', '[^/]+');
Route::get('/pacientes/{paciente}/cancelar', [PatientController::class, 'cancel'])->name('pacientes.cancel')->where('paciente', '[^/]+');
Route::get('/pacientes/{paciente}/expediente', [PatientController::class, 'show'])->name('pacientes.expediente');
Route::post('/pacientes/{patient}/expediente/consultas', [ClinicalRecordController::class, 'store'])->name('pacientes.clinical.store');
Route::put('/pacientes/{patient}/expediente/antecedentes', [ClinicalRecordController::class, 'updateMedicalHistory'])->name('pacientes.medical.update');
Route::put('/pacientes/{patient}/expediente/odontograma', [ClinicalRecordController::class, 'updateTooth'])->name('pacientes.odontogram.update');
Route::get('/pacientes/historial', [PatientController::class, 'history'])->name('pacientes.historial');

Route::get('/pacientes/tratamiento', [TreatmentController::class, 'create'])->name('pacientes.tratamiento');
Route::post('/pacientes/tratamientos', [TreatmentController::class, 'store'])->name('pacientes.tratamientos.store');
Route::get('/pacientes/tratamientos', [TreatmentController::class, 'index'])->name('pacientes.tratamientos');
Route::get('/pacientes/tratamientos/{id}/editar', [TreatmentController::class, 'edit'])->name('pacientes.tratamientos.edit');
Route::put('/pacientes/tratamientos/{id}', [TreatmentController::class, 'update'])->name('pacientes.tratamientos.update');
Route::get('/pacientes/tratamientos/{id}/eliminar', [TreatmentController::class, 'destroy'])->name('pacientes.tratamientos.delete');

// Dentistas
use App\Http\Controllers\DentistController;

Route::get('/dentistas', [DentistController::class, 'index'])->name('dentistas');
Route::get('/dentistas/crear', [DentistController::class, 'create'])->name('dentistas.create');
Route::post('/dentistas', [DentistController::class, 'store'])->name('dentistas.store');
Route::get('/dentistas/{id}/editar', [DentistController::class, 'edit'])->name('dentistas.edit');
Route::put('/dentistas/{id}', [DentistController::class, 'update'])->name('dentistas.update');
Route::get('/dentistas/{id}/eliminar', [DentistController::class, 'destroy'])->name('dentistas.delete');

Route::get('/inventario', [InventoryController::class, 'index'])->name('inventario');
Route::get('/inventario/crear', [InventoryController::class, 'create'])->name('inventario.create');
Route::post('/inventario', [InventoryController::class, 'store'])->name('inventario.store');
Route::get('/inventario/{id}/editar', [InventoryController::class, 'edit'])->name('inventario.edit');
Route::post('/inventario/{id}', [InventoryController::class, 'update'])->name('inventario.update');
Route::get('/inventario/{id}/eliminar', [InventoryController::class, 'destroy'])->name('inventario.delete');

Route::get('/citas', [AppointmentController::class, 'index'])->name('citas');
Route::get('/citas/crear', [AppointmentController::class, 'create'])->name('citas.create');
Route::post('/citas', [AppointmentController::class, 'store'])->name('citas.store');
Route::get('/citas/{id}/editar', [AppointmentController::class, 'edit'])->name('citas.edit');
Route::post('/citas/{id}', [AppointmentController::class, 'update'])->name('citas.update');
Route::get('/citas/{id}/confirmar', [AppointmentController::class, 'confirm'])->name('citas.confirmar');
Route::get('/citas/{id}/eliminar', [AppointmentController::class, 'destroy'])->name('citas.delete');
Route::get('/citas/{id}/cancelar', [AppointmentController::class, 'cancel'])->name('citas.cancelar');
Route::get('/citas/{id}/asistida', [AppointmentController::class, 'attended'])->name('citas.asistida');
Route::post('/citas/{id}/terminar', [AppointmentController::class, 'finish'])->name('citas.terminar');
Route::get('/citas/{id}/reprogramar', [AppointmentController::class, 'reprogram'])->name('citas.reprogramar');
Route::get('/citas/{id}/detalle', [AppointmentController::class, 'show'])->name('citas.show');

Route::get('/reportes', [\App\Http\Controllers\ReportController::class, 'index'])->name('reportes');

Route::get('/reportes/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reportes.export');

Route::get('/perfil', [\App\Http\Controllers\ProfileController::class, 'show'])->name('perfil');
Route::get('/perfil/editar', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('perfil.edit');
Route::post('/perfil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('perfil.update');
Route::post('/perfil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('perfil.password.update');
Route::post('/perfil/agenda', [\App\Http\Controllers\ProfileController::class, 'updateSchedule'])->name('perfil.schedule.update');
