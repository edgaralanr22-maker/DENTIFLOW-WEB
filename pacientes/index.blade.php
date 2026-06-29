@extends('layouts.app')

@section('title', 'Pacientes')

@section('content')

@php
    $pacientesCollection = $pacientes instanceof \Illuminate\Pagination\AbstractPaginator
        ? collect($pacientes->items())
        : collect($pacientes);

    $metricas = [
        [
            'label' => 'Total de pacientes',
            'value' => $resumen['total'] ?? $pacientesCollection->count(),
            'detail' => 'Expedientes registrados',
            'tone' => 'border-purple-200 bg-purple-50 text-[#4B136B]',
        ],
        [
            'label' => 'Activos',
            'value' => $resumen['activos'] ?? $pacientesCollection->where('estado', 'Activo')->count(),
            'detail' => 'En seguimiento regular',
            'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        ],
        [
            'label' => 'Pendientes',
            'value' => $resumen['pendientes'] ?? $pacientesCollection->where('estado', 'Pendiente')->count(),
            'detail' => 'Requieren contacto',
            'tone' => 'border-amber-200 bg-amber-50 text-amber-700',
        ],
        [
            'label' => 'Inactivos',
            'value' => $resumen['inactivos'] ?? $pacientesCollection->whereIn('estado', ['Inactivo', 'Cancelada'])->count(),
            'detail' => 'Sin atencion vigente',
            'tone' => 'border-rose-200 bg-rose-50 text-rose-700',
        ],
    ];

    $estadoClasses = [
        'Activo' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Pendiente' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'Inactivo' => 'bg-rose-100 text-rose-700 ring-rose-200',
        'Cancelada' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4B136B]/70">Directorio clinico</p>
            <h1 class="mt-2 text-4xl font-bold text-slate-900">Pacientes</h1>
            <p class="mt-2 max-w-2xl text-slate-500">
                Administra expedientes, estado de seguimiento y accesos rapidos a tratamientos.
            </p>
        </div>

        <a href="{{ route('pacientes.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#4B136B] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-800">
            Nuevo paciente
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($metricas as $metrica)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $metrica['label'] }}</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ $metrica['value'] }}</p>
                    </div>
                    <span class="h-3 w-3 rounded-full border {{ $metrica['tone'] }}"></span>
                </div>
                <p class="mt-3 text-sm text-slate-500">{{ $metrica['detail'] }}</p>
            </div>
        @endforeach
    </div>

    <form action="{{ route('pacientes') }}" method="GET" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-[minmax(220px,1.5fr)_minmax(160px,0.8fr)_minmax(170px,0.9fr)_auto] lg:items-end">
            <label class="block">
                <span class="text-sm font-medium text-slate-600">Buscar</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nombre, telefono o estado"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Estado</span>
                <select name="estado" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-700 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
                    <option value="Todos">Todos</option>
                    @foreach($estados ?? collect(['Activo', 'Pendiente', 'Inactivo']) as $estado)
                        <option value="{{ $estado }}" {{ request('estado', 'Todos') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Ordenar</span>
                <select name="order" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-700 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
                    <option value="visita_desc" {{ request('order', 'visita_desc') === 'visita_desc' ? 'selected' : '' }}>Visita reciente</option>
                    <option value="visita_asc" {{ request('order') === 'visita_asc' ? 'selected' : '' }}>Visita antigua</option>
                    <option value="nombre_asc" {{ request('order') === 'nombre_asc' ? 'selected' : '' }}>Nombre A-Z</option>
                    <option value="nombre_desc" {{ request('order') === 'nombre_desc' ? 'selected' : '' }}>Nombre Z-A</option>
                </select>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#4B136B] px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800 lg:flex-none">
                    Aplicar
                </button>

                @if(count(request()->query()) > 0)
                    <a href="{{ route('pacientes') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Limpiar
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Directorio de pacientes</h2>
                <p class="text-sm text-slate-500">Mostrando {{ $pacientesCollection->count() }} resultado{{ $pacientesCollection->count() === 1 ? '' : 's' }}</p>
            </div>
            <a href="{{ route('pacientes.create') }}" class="text-sm font-semibold text-[#4B136B] hover:text-purple-800">
                Agregar paciente
            </a>
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[980px] text-left">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Paciente</th>
                        <th class="px-5 py-4 font-semibold">Telefono</th>
                        <th class="px-5 py-4 font-semibold">Ultima visita</th>
                        <th class="px-5 py-4 font-semibold">Estado</th>
                        <th class="px-5 py-4 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pacientes as $paciente)
                        @php
                            $segments = explode(' ', trim($paciente['nombre'] ?? ''));
                            $initials = collect($segments)->filter()->map(fn($segment) => strtoupper(substr($segment, 0, 1)))->take(2)->join('');
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-purple-100 text-sm font-bold text-[#4B136B] ring-1 ring-purple-200">
                                        {{ $initials ?: 'PX' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $paciente['nombre'] }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Expediente disponible</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $paciente['telefono'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $paciente['ultima_visita'] }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $estadoClasses[$paciente['estado']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    {{ $paciente['estado'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-3 text-sm font-semibold">
                                    <a href="{{ route('pacientes.expediente', ['paciente' => $paciente['nombre']]) }}" class="text-[#4B136B] hover:text-purple-800">Expediente</a>
                                    <a href="{{ route('pacientes.tratamientos') }}" class="text-slate-600 hover:text-slate-900">Tratamientos</a>
                                    <a href="{{ route('pacientes.edit', ['paciente' => $paciente['id'] ?? $paciente['nombre']]) }}" class="text-slate-600 hover:text-slate-900">Editar</a>
                                    <a href="{{ route('pacientes.delete', ['paciente' => $paciente['nombre']]) }}" class="text-rose-600 hover:text-rose-700">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-12 text-center text-slate-500" colspan="5">
                                No se encontraron pacientes con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 lg:hidden">
            @forelse($pacientes as $paciente)
                @php
                    $segments = explode(' ', trim($paciente['nombre'] ?? ''));
                    $initials = collect($segments)->filter()->map(fn($segment) => strtoupper(substr($segment, 0, 1)))->take(2)->join('');
                @endphp
                <article class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-purple-100 text-sm font-bold text-[#4B136B] ring-1 ring-purple-200">
                                {{ $initials ?: 'PX' }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-semibold text-slate-900">{{ $paciente['nombre'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $paciente['telefono'] }}</p>
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $estadoClasses[$paciente['estado']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                            {{ $paciente['estado'] }}
                        </span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Ultima visita</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $paciente['ultima_visita'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Estado</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $paciente['estado'] }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm font-semibold">
                        <a href="{{ route('pacientes.expediente', ['paciente' => $paciente['nombre']]) }}" class="text-[#4B136B] hover:text-purple-800">Expediente</a>
                        <a href="{{ route('pacientes.tratamientos') }}" class="text-slate-600 hover:text-slate-900">Tratamientos</a>
                        <a href="{{ route('pacientes.edit', ['paciente' => $paciente['id'] ?? $paciente['nombre']]) }}" class="text-slate-600 hover:text-slate-900">Editar</a>
                        <a href="{{ route('pacientes.delete', ['paciente' => $paciente['nombre']]) }}" class="text-rose-600 hover:text-rose-700">Eliminar</a>
                    </div>
                </article>
            @empty
                <div class="px-5 py-12 text-center text-slate-500">
                    No se encontraron pacientes con los filtros actuales.
                </div>
            @endforelse
        </div>
    </div>

    @if(is_object($pacientes) && method_exists($pacientes, 'links'))
        <div class="rounded-lg bg-white p-4 shadow-sm">
            {{ $pacientes->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
