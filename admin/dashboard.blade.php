@extends('layouts.app')
@section('title', 'Administración del sistema')
@section('content')
<div class="space-y-6">
    @if(session('access_denied'))
        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100">!</span>{{ session('access_denied') }}</div>
    @endif
    <section>
        <p class="text-sm font-semibold text-[#7065f0]">Administración técnica</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight">Estado de DentiFlow</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Supervisa y configura la aplicación. Los datos clínicos y las tareas operativas están reservados para doctores y asistentes.</p>
    </section>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Estado', $system['status'], 'Sistema disponible', 'bg-emerald-50 text-emerald-600'],
            ['Usuarios', $system['users'], 'Cuentas registradas', 'bg-indigo-50 text-indigo-600'],
            ['Doctores', $system['doctors'], 'Personal clínico', 'bg-sky-50 text-sky-600'],
            ['Último respaldo', $system['lastBackup'], 'Copia automática', 'bg-amber-50 text-amber-600'],
        ] as [$label, $value, $detail, $color])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
                <span class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase {{ $color }}">{{ $label }}</span>
                <p class="mt-5 text-xl font-bold text-slate-800">{{ $value }}</p><p class="mt-1 text-xs text-slate-400">{{ $detail }}</p>
            </article>
        @endforeach
    </section>
    <section class="grid gap-5 lg:grid-cols-[1.4fr_1fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <h2 class="font-bold">Herramientas administrativas</h2><p class="mt-1 text-xs text-slate-400">Cambios técnicos solicitados por el equipo</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.settings') }}" class="rounded-xl border border-slate-100 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">⚙</span><strong class="mt-3 block text-sm">Configuración general</strong><span class="mt-1 block text-xs text-slate-400">Clínica, soporte y preferencias</span></a>
                <a href="{{ route('admin.settings') }}#modules" class="rounded-xl border border-slate-100 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">◫</span><strong class="mt-3 block text-sm">Módulos del sistema</strong><span class="mt-1 block text-xs text-slate-400">Habilitar funciones solicitadas</span></a>
            </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-[#17152d] p-6 text-white shadow-lg">
            <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-violet-200">Permisos</span>
            <h2 class="mt-4 text-lg font-bold">Rol aislado del área clínica</h2>
            <p class="mt-2 text-sm leading-6 text-white/55">Este perfil no puede consultar ni modificar pacientes, citas, expedientes, tratamientos o inventario.</p>
            <div class="mt-5 flex items-center gap-2 text-xs font-semibold text-emerald-300"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>Restricciones activas</div>
        </article>
    </section>
</div>
@endsection
