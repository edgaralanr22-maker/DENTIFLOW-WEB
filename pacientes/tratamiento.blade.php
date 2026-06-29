@extends('layouts.app')
@section('title', $edit ? 'Editar tratamiento' : 'Nuevo tratamiento')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-[#7065f0]">Catálogo clínico</p><h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $edit ? 'Editar tratamiento' : 'Nuevo tratamiento' }}</h1><p class="mt-2 text-sm text-slate-500">Define el servicio que podrá seleccionarse al agendar una cita.</p></div>
        <a href="{{ route('pacientes.tratamientos') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600">Volver al catálogo</a>
    </div>
    <form method="POST" action="{{ $edit ? route('pacientes.tratamientos.update', $tratamiento['id']) : route('pacientes.tratamientos.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(30,34,60,.04)] sm:p-8">
        @csrf
        @if($edit) @method('PUT') @endif
        @if($errors->any())<div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Nombre del tratamiento
                <input name="tratamiento" value="{{ old('tratamiento', $tratamiento['tratamiento'] ?? '') }}" placeholder="Ej. Limpieza dental" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3.5 font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Tipo
                <select name="tipo" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3.5 font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
                    @foreach(['Preventivo', 'Correctivo', 'Estético', 'Quirúrgico', 'Diagnóstico', 'Restaurativo'] as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo', $tratamiento['tipo'] ?? '') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-semibold text-slate-700">Costo
                <div class="relative mt-2"><span class="absolute left-4 top-3.5 text-slate-400">$</span><input type="number" min="0" step="0.01" name="costo" value="{{ old('costo', $tratamiento['costo'] ?? '') }}" placeholder="0.00" required class="w-full rounded-xl border border-slate-200 py-3.5 pl-8 pr-4 font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100"></div>
            </label>
            <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">Descripción
                <textarea name="descripcion" rows="5" placeholder="Describe el procedimiento, alcance o indicaciones" class="mt-2 w-full resize-none rounded-xl border border-slate-200 px-4 py-3.5 font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">{{ old('descripcion', $tratamiento['descripcion'] ?? '') }}</textarea>
            </label>
        </div>
        <div class="mt-7 flex justify-end gap-3"><a href="{{ route('pacientes.tratamientos') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600">Cancelar</a><button class="rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200">{{ $edit ? 'Guardar cambios' : 'Registrar tratamiento' }}</button></div>
    </form>
</div>
@endsection
