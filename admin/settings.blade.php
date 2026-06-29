@extends('layouts.app')
@section('title', 'Configuración')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <a href="{{ route('inicio') }}" class="text-xs font-semibold text-[#7065f0]">← Estado del sistema</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight">Configuración de la aplicación</h1>
        <p class="mt-2 text-sm text-slate-500">Realiza ajustes generales solicitados por doctores y asistentes.</p>
    </div>
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('admin.settings.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
        @csrf
        <h2 class="font-bold">Información general</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <label class="text-sm font-semibold text-slate-700">Nombre de la clínica<input name="clinic_name" value="{{ old('clinic_name', 'Clínica DentiFlow') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100"></label>
            <label class="text-sm font-semibold text-slate-700">Correo de soporte<input type="email" name="support_email" value="{{ old('support_email', 'soporte@dentiflow.com') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100"></label>
        </div>
        <div id="modules" class="mt-8 border-t border-slate-100 pt-6">
            <h2 class="font-bold">Módulos y preferencias</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach([
                    ['Recordatorios automáticos', 'Enviar avisos de citas al personal', true],
                    ['Alertas de inventario', 'Notificar cuando un material llegue al mínimo', true],
                    ['Modo de mantenimiento', 'Bloquear temporalmente el acceso operativo', false],
                ] as [$title, $detail, $checked])
                    <label class="flex cursor-pointer items-center gap-4 py-4"><span class="flex-1"><strong class="block text-sm text-slate-700">{{ $title }}</strong><span class="text-xs text-slate-400">{{ $detail }}</span></span><input type="checkbox" name="modules[]" value="{{ $loop->index }}" @checked($checked) class="h-5 w-5 rounded border-slate-300 text-[#7065f0] focus:ring-[#7065f0]"></label>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex justify-end"><button class="rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200">Guardar cambios</button></div>
    </form>
</div>
@endsection
