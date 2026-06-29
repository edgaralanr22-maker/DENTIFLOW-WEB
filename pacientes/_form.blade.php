@php
    $emergency = $paciente['contacto_emergencia'] ?? [];
    $fieldClass = 'mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100';
@endphp
<div class="mx-auto max-w-6xl space-y-6">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-[#7065f0]">Expediente clínico</p><h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $title }}</h1><p class="mt-2 text-sm text-slate-500">{{ $subtitle }}</p></div>
        <a href="{{ route('pacientes') }}" class="inline-flex justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm">Volver a pacientes</a>
    </header>

    @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><p class="mb-2 font-bold">Revisa la información:</p>@foreach($errors->all() as $error)<p>• {{ $error }}</p>@endforeach</div>@endif

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="7" r="4"/><path d="M5 22v-3a7 7 0 0 1 14 0v3"/></svg></span><div><h2 class="font-bold text-slate-800">Información personal</h2><p class="text-xs text-slate-400">Datos principales para identificar al paciente</p></div></div>
            <div class="grid gap-5 p-6 md:grid-cols-2">
                <label class="text-sm font-semibold text-slate-700">Nombre completo <span class="text-rose-500">*</span><input name="nombre" value="{{ old('nombre', $paciente['nombre'] ?? '') }}" placeholder="Nombre y apellidos" required class="{{ $fieldClass }}"></label>
                <label class="text-sm font-semibold text-slate-700">Correo electrónico<input type="email" name="email" value="{{ old('email', $paciente['email'] ?? '') }}" placeholder="paciente@correo.com" class="{{ $fieldClass }}"><span class="mt-1.5 block text-[10px] font-normal text-slate-400">Se utilizará para recordatorios y comunicaciones.</span></label>
                <label class="text-sm font-semibold text-slate-700">Teléfono <span class="text-rose-500">*</span><input type="tel" name="telefono" value="{{ old('telefono', $paciente['telefono'] ?? '') }}" placeholder="222 000 0000" required class="{{ $fieldClass }}"></label>
                <label class="text-sm font-semibold text-slate-700">Dirección<input name="direccion" value="{{ old('direccion', $paciente['direccion'] ?? '') }}" placeholder="Calle, número, colonia y ciudad" class="{{ $fieldClass }}"></label>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2ZM7 2v4m10-4v4M3 9h18"/></svg></span><div><h2 class="font-bold text-slate-800">Información del expediente</h2><p class="text-xs text-slate-400">Seguimiento y cobertura del paciente</p></div></div>
            <div class="grid gap-5 p-6 md:grid-cols-2 lg:grid-cols-4">
                <label class="text-sm font-semibold text-slate-700">Última visita<input type="date" name="ultima_visita" value="{{ old('ultima_visita', $paciente['ultima_visita_input'] ?? '') }}" class="{{ $fieldClass }}"></label>
                <label class="text-sm font-semibold text-slate-700">Estado<select name="estado" class="{{ $fieldClass }}">@foreach(['Activo', 'Pendiente', 'Inactivo'] as $estado)<option value="{{ $estado }}" @selected(old('estado', $paciente['estado'] ?? 'Activo') === $estado)>{{ $estado }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold text-slate-700">Seguro dental<input name="seguro" value="{{ old('seguro', $paciente['seguro'] ?? '') }}" placeholder="Nombre de aseguradora" class="{{ $fieldClass }}"></label>
                <label class="text-sm font-semibold text-slate-700">Número de póliza<input name="poliza" value="{{ old('poliza', $paciente['poliza'] ?? '') }}" placeholder="Póliza o membresía" class="{{ $fieldClass }}"></label>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">!</span><div><h2 class="font-bold text-slate-800">Contacto de emergencia</h2><p class="text-xs text-slate-400">Información opcional para situaciones importantes</p></div></div>
            <div class="grid gap-5 p-6 md:grid-cols-3">
                <label class="text-sm font-semibold text-slate-700">Nombre<input name="contacto_emergencia[nombre]" value="{{ old('contacto_emergencia.nombre', $emergency['nombre'] ?? '') }}" placeholder="Nombre del contacto" class="{{ $fieldClass }}"></label>
                <label class="text-sm font-semibold text-slate-700">Teléfono<input type="tel" name="contacto_emergencia[telefono]" value="{{ old('contacto_emergencia.telefono', $emergency['telefono'] ?? '') }}" placeholder="222 000 0000" class="{{ $fieldClass }}"></label>
                <label class="text-sm font-semibold text-slate-700">Parentesco<input name="contacto_emergencia[relacion]" value="{{ old('contacto_emergencia.relacion', $emergency['relacion'] ?? '') }}" placeholder="Ej. Familiar, pareja" class="{{ $fieldClass }}"></label>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('pacientes') }}" class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-600">Cancelar</a><button class="rounded-xl bg-[#7065f0] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200">{{ $submitLabel }}</button></div>
    </form>
</div>
