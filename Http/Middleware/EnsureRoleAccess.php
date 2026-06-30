<?php

namespace App\Http\Middleware;

use App\Models\ClinicSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if (!$routeName || Str::is(['role.select', 'login', 'register', 'password.*', 'storage.*'], $routeName)) {
            return $next($request);
        }

        $role = session('access_role');

        if (!$role) {
            return redirect()->route('role.select');
        }

        if ($this->maintenanceBlocks($role, $routeName)) {
            return redirect()->route('inicio')->with('access_denied', 'El sistema esta en modo mantenimiento. Solo administracion puede operar modulos internos.');
        }

        $permissions = [
            'admin' => ['inicio', 'admin.*', 'dentistas*', 'perfil*', 'logout'],
            'doctor' => ['inicio', 'calendario', 'citas*', 'pacientes*', 'reportes*', 'perfil*', 'logout'],
            'asistente' => ['inicio', 'calendario', 'citas*', 'pacientes', 'pacientes.create', 'pacientes.store', 'pacientes.edit', 'pacientes.update', 'pacientes.expediente', 'pacientes.historial', 'inventario*', 'perfil*', 'logout'],
        ];

        if (!isset($permissions[$role]) || !Str::is($permissions[$role], $routeName)) {
            return redirect()->route('inicio')->with('access_denied', 'Tu perfil no tiene permiso para acceder a esa sección.');
        }

        return $next($request);
    }

    private function maintenanceBlocks(string $role, string $routeName): bool
    {
        if ($role === 'admin' || ! ClinicSetting::current()->maintenance_mode_enabled) {
            return false;
        }

        // Durante mantenimiento se conserva inicio/perfil/logout para evitar dejar usuarios atrapados.
        return ! Str::is(['inicio', 'perfil*', 'logout'], $routeName);
    }
}
