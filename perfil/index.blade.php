@extends('layouts.app')

@section('title','Perfil')

@section('content')

@php
    $agendaPredeterminada = [
        ['dia' => 'Lun', 'horario' => '9:00 - 17:00'],
        ['dia' => 'Mar', 'horario' => '9:00 - 17:00'],
        ['dia' => 'Mié', 'horario' => '9:00 - 17:00'],
        ['dia' => 'Jue', 'horario' => '9:00 - 17:00'],
        ['dia' => 'Vie', 'horario' => '9:00 - 15:00'],
    ];

    $actividades = [
        ['texto' => 'Confirmó cita con Juan Pérez', 'tiempo' => 'hace 2h'],
        ['texto' => 'Actualizó su horario de atención', 'tiempo' => 'ayer'],
        ['texto' => 'Editó el perfil de Ana Gómez', 'tiempo' => 'ayer'],
        ['texto' => 'Revisó reporte de ingresos', 'tiempo' => 'hace 3 días'],
        ['texto' => 'Confirmó paciente nuevo para revisión', 'tiempo' => 'hace 5 días'],
    ];

    $notificaciones = [
        ['id' => 'email', 'label' => 'Notificaciones por email', 'active' => true],
        ['id' => 'recordatorios', 'label' => 'Recordatorios de citas', 'active' => true],
    ];

    $ultimoLogin = 'Hace 3 horas';

    $initials = collect(explode(' ', $usuario['nombre']))->map(fn($item) => substr($item, 0, 1))->join('');
@endphp

<div class="max-w-7xl mx-auto">

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Perfil</h1>
            <p class="text-gray-500 mt-2">Información de tu cuenta, seguridad y actividad reciente.</p>
        </div>

        <a href="{{ route('perfil.edit') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#4B136B] px-6 py-3 text-white hover:bg-purple-800 transition">
            Editar perfil
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-3xl bg-green-50 border border-green-200 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">

        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-lg p-6">
                <div class="flex flex-col items-center text-center gap-4">
                    <button type="button" class="flex h-24 w-24 items-center justify-center rounded-full bg-purple-100 text-3xl font-bold text-[#4B136B] transition hover:bg-purple-200">
                        {{ strtoupper($initials) }}
                    </button>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Avatar de perfil</p>
                        <p class="mt-2 text-gray-700">Haz clic para subir o cambiar foto</p>
                    </div>
                </div>

                <div class="mt-8 space-y-6">
                    <div class="rounded-2xl bg-gray-50 p-5">
                        <p class="text-sm text-gray-500">Nombre</p>
                        <p class="mt-2 text-lg font-semibold text-gray-800">{{ $usuario['nombre'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-5">
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="mt-2 text-lg font-semibold text-gray-800">{{ $usuario['email'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-5">
                        <p class="text-sm text-gray-500">Teléfono</p>
                        <p class="mt-2 text-lg font-semibold text-gray-800">{{ $usuario['telefono'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Seguridad</h2>
                        <p class="text-sm text-gray-500">Configuraciones de acceso y protección.</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <button type="button" class="w-full rounded-2xl bg-[#4B136B] px-5 py-3 text-white hover:bg-purple-800 transition">Cambiar contraseña</button>

                    <div class="rounded-2xl bg-gray-50 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Verificación en dos pasos</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" checked class="peer sr-only" />
                                <div class="h-6 w-11 rounded-full bg-gray-300 transition peer-checked:bg-[#4B136B]"></div>
                                <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 p-5">
                        <p class="text-sm text-gray-500">Último inicio de sesión</p>
                        <p class="mt-2 text-lg font-semibold text-gray-800">{{ $ultimoLogin }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Detalles profesionales</h2>
                        <p class="text-sm text-gray-500">Horario y métricas clave del consultorio.</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50 p-6">
                        <p class="text-sm text-gray-500">Especialidad</p>
                        <p class="mt-3 text-lg font-semibold text-gray-800">{{ $usuario['especialidad'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-6">
                        <p class="text-sm text-gray-500">Citas confirmadas</p>
                        <p class="mt-3 text-lg font-semibold text-gray-800">{{ $citasConfirmadas }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-6">
                        <p class="text-sm text-gray-500">Pacientes activos</p>
                        <p class="mt-3 text-lg font-semibold text-gray-800">{{ $pacientesActivos }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div><p class="text-sm font-semibold text-gray-700">Agenda semanal</p><p class="mt-1 text-xs text-gray-400">Tu disponibilidad habitual</p></div>
                            @if(session('access_role') === 'doctor')
                                <button type="button" onclick="document.getElementById('schedule-editor').showModal()" class="rounded-xl bg-[#7065f0] px-3 py-2 text-xs font-bold text-white">Editar horario</button>
                            @endif
                        </div>
                        <div class="mt-4 space-y-2">
                            @foreach($agenda as $item)
                                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3">
                                    <span class="text-sm font-medium text-gray-700">{{ $item['dia'] }}</span>
                                    <span class="text-xs font-semibold {{ $item['enabled'] ? 'text-gray-500' : 'text-gray-300' }}">{{ $item['horario'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Preferencias de notificaciones</h2>
                        <p class="text-sm text-gray-500">Activa o desactiva alertas para tu perfil.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($notificaciones as $item)
                        <label class="flex items-center justify-between rounded-2xl bg-gray-50 border border-gray-200 px-5 py-4">
                            <span class="text-gray-700">{{ $item['label'] }}</span>
                            <input type="checkbox" {{ $item['active'] ? 'checked' : '' }} class="h-5 w-5 rounded border-gray-300 text-[#4B136B] focus:ring-[#4B136B]" />
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6 mt-6">
        <div class="mb-5">
            <h2 class="text-2xl font-bold text-gray-800">Actividad reciente</h2>
            <p class="text-sm text-gray-500">Últimas acciones realizadas en tu cuenta.</p>
        </div>

        <div class="space-y-4">
            @foreach($actividades as $actividad)
                <div class="flex items-start gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <span class="mt-1 inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#F3E8FF] text-[#4B136B]">✓</span>
                    <div>
                        <p class="text-gray-800">{{ $actividad['texto'] }}</p>
                        <p class="text-sm text-gray-500">{{ $actividad['tiempo'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if(session('access_role') === 'doctor')
        <dialog id="schedule-editor" class="m-auto w-[calc(100%-2rem)] max-w-2xl rounded-3xl p-0 shadow-2xl backdrop:bg-slate-950/50">
            <form method="POST" action="{{ route('perfil.schedule.update') }}" class="bg-white">
                @csrf
                <div class="flex items-start justify-between border-b border-slate-100 p-6">
                    <div><h2 class="text-xl font-bold text-slate-800">Editar agenda semanal</h2><p class="mt-1 text-sm text-slate-400">Elige tus días y horas disponibles.</p></div>
                    <button type="button" onclick="document.getElementById('schedule-editor').close()" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100">✕</button>
                </div>
                @if($errors->any())<div class="mx-6 mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
                <div class="max-h-[60vh] space-y-3 overflow-y-auto p-6">
                    @foreach($agenda as $item)
                        <div class="grid items-center gap-3 rounded-2xl border border-slate-200 p-4 sm:grid-cols-[90px_1fr_1fr]">
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input type="hidden" name="schedule[{{ $item['weekday'] }}][enabled]" value="0">
                                <input type="checkbox" name="schedule[{{ $item['weekday'] }}][enabled]" value="1" @checked($item['enabled']) class="h-4 w-4 rounded border-slate-300 text-[#7065f0] focus:ring-[#7065f0]">
                                {{ $item['dia'] }}
                            </label>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Entrada
                                <input type="time" name="schedule[{{ $item['weekday'] }}][start_time]" value="{{ $item['start_time'] }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700">
                            </label>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Salida
                                <input type="time" name="schedule[{{ $item['weekday'] }}][end_time]" value="{{ $item['end_time'] }}" class="mt-1 block w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700">
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end gap-3 border-t border-slate-100 p-5">
                    <button type="button" onclick="document.getElementById('schedule-editor').close()" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600">Cancelar</button>
                    <button class="rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200">Guardar agenda</button>
                </div>
            </form>
        </dialog>
    @endif
</div>

@endsection
