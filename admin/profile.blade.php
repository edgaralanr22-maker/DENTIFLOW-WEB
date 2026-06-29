@extends('layouts.app')
@section('title', 'Perfil administrativo')
@section('content')
@php
    $initials = collect(explode(' ', $usuario->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->join('');
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#7065f0]">Cuenta del sistema</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Perfil administrativo</h1>
            <p class="mt-2 text-sm text-slate-500">Información de acceso, seguridad y alcance de tu cuenta técnica.</p>
        </div>
        <a href="{{ route('perfil.edit') }}" class="inline-flex justify-center rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200">Editar perfil</a>
    </div>

    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>@endif

    <div class="grid gap-5 lg:grid-cols-[.85fr_1.5fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-[#eeecff] text-2xl font-bold text-[#7065f0]">{{ mb_strtoupper($initials) }}</div>
            <h2 class="mt-5 text-xl font-bold">{{ $usuario->name }}</h2>
            <p class="mt-1 text-sm text-slate-400">{{ $usuario->email }}</p>
            <span class="mt-4 inline-flex rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600">Administrador técnico</span>
            <div class="mt-6 border-t border-slate-100 pt-5 text-left">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Identificador</p>
                <p class="mt-1 text-sm font-semibold text-slate-700">ADM-{{ str_pad($usuario->id, 4, '0', STR_PAD_LEFT) }}</p>
            </div>
        </article>

        <div class="space-y-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
                <h2 class="font-bold">Información de la cuenta</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nombre</p><p class="mt-2 text-sm font-semibold text-slate-700">{{ $usuario->name }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Correo</p><p class="mt-2 text-sm font-semibold text-slate-700">{{ $usuario->email }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rol</p><p class="mt-2 text-sm font-semibold text-slate-700">Administrador de aplicación</p></div>
                    <div class="rounded-xl bg-slate-50 p-4"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estado</p><p class="mt-2 flex items-center gap-2 text-sm font-semibold text-emerald-600"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Activo</p></div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-[#17152d] p-6 text-white shadow-lg">
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-violet-300">⌁</span>
                    <div><h2 class="font-bold">Alcance del perfil</h2><p class="mt-2 text-sm leading-6 text-white/55">Puede configurar módulos y mantener la aplicación. No tiene acceso a expedientes, pacientes, citas, tratamientos ni tareas de recepción.</p></div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
                <div class="flex items-center justify-between"><div><h2 class="font-bold">Seguridad</h2><p class="mt-1 text-xs text-slate-400">Protección de la cuenta administrativa</p></div><span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-600">PROTEGIDA</span></div>
                <div class="mt-5 flex items-center justify-between rounded-xl border border-slate-100 p-4"><div><p class="text-sm font-semibold text-slate-700">Contraseña</p><p class="text-xs text-slate-400">Actualizada recientemente</p></div><button class="text-xs font-bold text-[#7065f0]">Cambiar</button></div>
            </article>
        </div>
    </div>
</div>
@endsection
