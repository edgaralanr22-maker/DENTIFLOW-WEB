@extends('layouts.app')
@section('title', 'Editar perfil administrativo')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="{{ route('perfil') }}" class="text-xs font-semibold text-[#7065f0]">← Volver al perfil</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight">Editar perfil administrativo</h1>
        <p class="mt-2 text-sm text-slate-500">Actualiza la identidad y el correo de acceso de la cuenta técnica.</p>
    </div>
    <form action="{{ route('perfil.update') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(30,34,60,.04)] sm:p-8">
        @csrf
        @if($errors->any())<div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="space-y-5">
            <label class="block text-sm font-semibold text-slate-700">Nombre visible
                <input type="text" name="nombre" value="{{ old('nombre', $usuario->name) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3.5 font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
            </label>
            <label class="block text-sm font-semibold text-slate-700">Correo administrativo
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3.5 font-normal outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
            </label>
            <div class="rounded-xl bg-amber-50 p-4 text-xs leading-5 text-amber-700">Este correo identifica la cuenta técnica. Los cambios no modifican ningún perfil de doctor o asistente.</div>
        </div>
        <div class="mt-7 flex justify-end gap-3"><a href="{{ route('perfil') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600">Cancelar</a><button class="rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200">Guardar cambios</button></div>
    </form>
</div>
@endsection
