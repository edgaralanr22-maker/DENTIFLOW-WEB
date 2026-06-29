@extends('layouts.app')
@section('title', 'Tratamientos')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold text-[#7065f0]">Catálogo clínico</p><h1 class="mt-1 text-3xl font-bold tracking-tight">Tratamientos</h1><p class="mt-2 text-sm text-slate-500">Administra los servicios disponibles para seleccionar al agendar una cita.</p></div>
        <a href="{{ route('pacientes.tratamiento') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200"><span class="text-lg">+</span>Nuevo tratamiento</a>
    </div>
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>@endif

    <section class="grid gap-4 sm:grid-cols-3">
        @foreach([
            ['Tratamientos', $resumen['total'], 'Servicios registrados', 'bg-indigo-50 text-indigo-600'],
            ['Categorías', $resumen['tipos'], 'Tipos disponibles', 'bg-emerald-50 text-emerald-600'],
            ['Costo promedio', '$'.number_format($resumen['promedio'], 0, ',', '.'), 'Promedio del catálogo', 'bg-amber-50 text-amber-600'],
        ] as [$label, $value, $detail, $color])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)]"><span class="inline-flex rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase {{ $color }}">{{ $label }}</span><p class="mt-4 text-2xl font-bold">{{ $value }}</p><p class="mt-1 text-xs text-slate-400">{{ $detail }}</p></article>
        @endforeach
    </section>

    <form method="GET" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_200px_180px_auto]">
        <input name="search" value="{{ request('search') }}" placeholder="Buscar tratamiento, tipo o descripción…" class="rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-[#7065f0] focus:ring-4 focus:ring-indigo-100">
        <select name="tipo" class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-600">
            <option value="Todos">Todos los tipos</option>
            @foreach($tipos as $tipo)<option value="{{ $tipo }}" @selected(request('tipo', 'Todos') === $tipo)>{{ $tipo }}</option>@endforeach
        </select>
        <select name="order" class="rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-600">
            <option value="nombre_asc">Nombre A–Z</option><option value="nombre_desc" @selected(request('order') === 'nombre_desc')>Nombre Z–A</option><option value="costo_desc" @selected(request('order') === 'costo_desc')>Mayor costo</option><option value="costo_asc" @selected(request('order') === 'costo_asc')>Menor costo</option>
        </select>
        <button class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white">Filtrar</button>
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left">
                <thead class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-bold uppercase tracking-wider text-slate-400"><tr><th class="px-5 py-4">Tratamiento</th><th class="px-5 py-4">Tipo</th><th class="px-5 py-4">Descripción</th><th class="px-5 py-4 text-right">Costo</th><th class="px-5 py-4 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tratamientos as $item)
                        <tr class="hover:bg-slate-50/60"><td class="px-5 py-4 font-semibold text-slate-800">{{ $item['tratamiento'] }}</td><td class="px-5 py-4"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-bold text-indigo-600">{{ $item['tipo'] }}</span></td><td class="max-w-sm px-5 py-4 text-xs text-slate-500">{{ $item['descripcion'] ?: 'Sin descripción' }}</td><td class="px-5 py-4 text-right font-bold">${{ number_format($item['costo'], 2, '.', ',') }}</td><td class="px-5 py-4"><div class="flex justify-end gap-3 text-xs font-bold"><a href="{{ route('pacientes.tratamientos.edit', $item['id']) }}" class="text-[#7065f0]">Editar</a><a href="{{ route('pacientes.tratamientos.delete', $item['id']) }}" class="text-rose-500">Eliminar</a></div></td></tr>
                    @empty<tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-400">No hay tratamientos registrados.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse($tratamientos as $item)<article class="p-5"><div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-800">{{ $item['tratamiento'] }}</h2><span class="mt-2 inline-flex rounded-full bg-indigo-50 px-2 py-1 text-[10px] font-bold text-indigo-600">{{ $item['tipo'] }}</span></div><strong>${{ number_format($item['costo'], 2) }}</strong></div><p class="mt-3 text-xs leading-5 text-slate-500">{{ $item['descripcion'] ?: 'Sin descripción' }}</p><div class="mt-4 flex gap-3 text-xs font-bold"><a href="{{ route('pacientes.tratamientos.edit', $item['id']) }}" class="text-[#7065f0]">Editar</a><a href="{{ route('pacientes.tratamientos.delete', $item['id']) }}" class="text-rose-500">Eliminar</a></div></article>@empty<div class="p-10 text-center text-sm text-slate-400">No hay tratamientos registrados.</div>@endforelse
        </div>
    </section>
</div>
@endsection
