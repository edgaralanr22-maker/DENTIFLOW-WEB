@extends('layouts.app')

@section('title','Gestion de Citas')

@section('content')

@php
    $citasCollection = $citas instanceof \Illuminate\Pagination\AbstractPaginator
        ? collect($citas->items())
        : collect($citas);

    $totalResultados = $citas instanceof \Illuminate\Pagination\AbstractPaginator
        ? $citas->total()
        : $citasCollection->count();

    $metricas = [
        [
            'label' => 'Citas de hoy',
            'value' => $resumen['hoy'] ?? $citasCollection->filter(fn($cita) => date('Y-m-d', strtotime($cita['fecha'])) === date('Y-m-d'))->count(),
            'detail' => 'Agenda del dia',
            'tone' => 'border-purple-200 bg-purple-50 text-[#4B136B]',
        ],
        [
            'label' => 'Confirmadas',
            'value' => $resumen['confirmadas'] ?? $citasCollection->where('estado', 'Confirmada')->count(),
            'detail' => 'Listas para atender',
            'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        ],
        [
            'label' => 'Pendientes',
            'value' => $resumen['pendientes'] ?? $citasCollection->where('estado', 'Pendiente')->count(),
            'detail' => 'Requieren confirmacion',
            'tone' => 'border-amber-200 bg-amber-50 text-amber-700',
        ],
        [
            'label' => 'Proximas',
            'value' => $resumen['proximas'] ?? $citasCollection->count(),
            'detail' => 'Por atender',
            'tone' => 'border-sky-200 bg-sky-50 text-sky-700',
        ],
    ];

    $estadoClasses = [
        'Confirmada' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'Pendiente' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'Cancelada' => 'bg-rose-100 text-rose-700 ring-rose-200',
        'Asistida' => 'bg-sky-100 text-sky-700 ring-sky-200',
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4B136B]/70">Agenda clinica</p>
            <h1 class="mt-2 text-4xl font-bold text-slate-900">Gestion de citas</h1>
            <p class="mt-2 max-w-2xl text-slate-500">
                Organiza la agenda diaria, confirma asistencia y da seguimiento a cada cita.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('calendario') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Ver calendario
            </a>
            <a href="{{ route('citas.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#4B136B] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-800">
                Nueva cita
            </a>
        </div>
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

    <form action="{{ route('citas') }}" method="GET" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 xl:grid-cols-[minmax(220px,1.3fr)_minmax(150px,0.75fr)_minmax(190px,0.9fr)_minmax(150px,0.75fr)_minmax(150px,0.8fr)_auto] xl:items-end">
            <label class="block">
                <span class="text-sm font-medium text-slate-600">Buscar</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Paciente, dentista o tipo"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Estado</span>
                <select name="estado" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-700 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
                    <option value="Todos">Todos</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ request('estado', 'Todos') === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Dentista</span>
                <select name="dentista" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-700 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
                    <option value="Todos">Todos</option>
                    @foreach($dentistas as $dentista)
                        <option value="{{ $dentista }}" {{ request('dentista', 'Todos') === $dentista ? 'selected' : '' }}>{{ $dentista }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Fecha</span>
                <input
                    type="date"
                    name="fecha"
                    value="{{ request('fecha') }}"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Ordenar</span>
                <select name="order" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-700 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
                    <option value="fecha_asc" {{ request('order', 'fecha_asc') === 'fecha_asc' ? 'selected' : '' }}>Proxima primero</option>
                    <option value="fecha_desc" {{ request('order') === 'fecha_desc' ? 'selected' : '' }}>Reciente primero</option>
                    <option value="paciente_asc" {{ request('order') === 'paciente_asc' ? 'selected' : '' }}>Paciente A-Z</option>
                    <option value="dentista_asc" {{ request('order') === 'dentista_asc' ? 'selected' : '' }}>Dentista A-Z</option>
                </select>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#4B136B] px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800 xl:flex-none">
                    Aplicar
                </button>

                @if(count(request()->query()) > 0)
                    <a href="{{ route('citas') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Limpiar
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Agenda de citas</h2>
                <p class="text-sm text-slate-500">Mostrando {{ $totalResultados }} resultado{{ $totalResultados === 1 ? '' : 's' }}</p>
            </div>
            <a href="{{ route('citas.create') }}" class="text-sm font-semibold text-[#4B136B] hover:text-purple-800">
                Agendar cita
            </a>
        </div>

        <div class="hidden overflow-x-auto xl:block">
            <table class="w-full min-w-[1120px] text-left">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Paciente</th>
                        <th class="px-5 py-4 font-semibold">Fecha y hora</th>
                        <th class="px-5 py-4 font-semibold">Dentista</th>
                        <th class="px-5 py-4 font-semibold">Tipo</th>
                        <th class="px-5 py-4 font-semibold">Estado</th>
                        <th class="px-5 py-4 text-right font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($citas as $cita)
                        @php
                            $fechaCita = date('Y-m-d', strtotime($cita['fecha']));
                            $isToday = $fechaCita === date('Y-m-d');
                            $isPast = $fechaCita < date('Y-m-d');
                        @endphp
                        <tr class="transition {{ $isToday ? 'bg-purple-50/70 hover:bg-purple-50' : 'hover:bg-slate-50' }}">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-900">{{ $cita['paciente'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">Cita #{{ $cita['id'] }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-800">{{ date('d/m/Y', strtotime($cita['fecha'])) }}</p>
                                <p class="mt-1 text-sm {{ $isPast ? 'text-rose-600' : 'text-slate-500' }}">{{ $cita['hora'] }}{{ $isToday ? ' · Hoy' : '' }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $cita['dentista'] }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $cita['tipo'] }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $estadoClasses[$cita['estado']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                    {{ $cita['estado'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap justify-end gap-x-3 gap-y-2 text-sm font-semibold">
                                    <a href="{{ route('citas.show', ['id' => $cita['id']]) }}" class="text-[#4B136B] hover:text-purple-800">Detalle</a>
                                    <a href="{{ route('citas.edit', ['id' => $cita['id']]) }}" class="text-slate-600 hover:text-slate-900">Editar</a>
                                    @if($cita['estado'] === 'Pendiente')
                                        <a href="{{ route('citas.confirmar', ['id' => $cita['id']]) }}" class="text-emerald-700 hover:text-emerald-800">Confirmar</a>
                                    @endif
                                    @if($cita['estado'] !== 'Asistida')
                                        <a href="{{ route('citas.asistida', ['id' => $cita['id']]) }}" class="text-sky-700 hover:text-sky-800">Asistida</a>
                                    @endif
                                    <a href="{{ route('citas.cancelar', ['id' => $cita['id']]) }}" class="text-amber-700 hover:text-amber-800">Cancelar</a>
                                    <a href="{{ route('citas.delete', ['id' => $cita['id']]) }}" class="text-rose-600 hover:text-rose-700">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-5 py-12 text-center text-slate-500" colspan="6">
                                No hay citas que coincidan con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 xl:hidden">
            @forelse($citas as $cita)
                @php
                    $fechaCita = date('Y-m-d', strtotime($cita['fecha']));
                    $isToday = $fechaCita === date('Y-m-d');
                    $isPast = $fechaCita < date('Y-m-d');
                @endphp
                <article class="p-5 {{ $isToday ? 'bg-purple-50/70' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Cita #{{ $cita['id'] }}</p>
                            <h3 class="mt-1 text-base font-semibold text-slate-900">{{ $cita['paciente'] }}</h3>
                        </div>
                        <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $estadoClasses[$cita['estado']] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                            {{ $cita['estado'] }}
                        </span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-slate-500">Fecha</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ date('d/m/Y', strtotime($cita['fecha'])) }}{{ $isToday ? ' · Hoy' : '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Hora</dt>
                            <dd class="mt-1 font-medium {{ $isPast ? 'text-rose-600' : 'text-slate-800' }}">{{ $cita['hora'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Dentista</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $cita['dentista'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Tipo</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $cita['tipo'] }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm font-semibold">
                        <a href="{{ route('citas.show', ['id' => $cita['id']]) }}" class="text-[#4B136B] hover:text-purple-800">Detalle</a>
                        <a href="{{ route('citas.edit', ['id' => $cita['id']]) }}" class="text-slate-600 hover:text-slate-900">Editar</a>
                        @if($cita['estado'] === 'Pendiente')
                            <a href="{{ route('citas.confirmar', ['id' => $cita['id']]) }}" class="text-emerald-700 hover:text-emerald-800">Confirmar</a>
                        @endif
                        @if($cita['estado'] !== 'Asistida')
                            <a href="{{ route('citas.asistida', ['id' => $cita['id']]) }}" class="text-sky-700 hover:text-sky-800">Asistida</a>
                        @endif
                        <a href="{{ route('citas.cancelar', ['id' => $cita['id']]) }}" class="text-amber-700 hover:text-amber-800">Cancelar</a>
                        <a href="{{ route('citas.delete', ['id' => $cita['id']]) }}" class="text-rose-600 hover:text-rose-700">Eliminar</a>
                    </div>
                </article>
            @empty
                <div class="px-5 py-12 text-center text-slate-500">
                    No hay citas que coincidan con los filtros actuales.
                </div>
            @endforelse
        </div>
    </div>

    @if(is_object($citas) && method_exists($citas, 'links'))
        <div class="rounded-lg bg-white p-4 shadow-sm">
            {{ $citas->links('pagination::tailwind') }}
        </div>
    @endif
</div>

@endsection
