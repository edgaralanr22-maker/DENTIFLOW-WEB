@extends('layouts.app')
@section('title', 'Agenda')
@section('content')
@php
    $role = session('access_role');
    $isDoctor = $role === 'doctor';
    $statusStyles = [
        'Confirmada' => 'bg-emerald-50 text-emerald-700',
        'Pendiente' => 'bg-amber-50 text-amber-700',
        'Cancelada' => 'bg-rose-50 text-rose-700',
        'Asistida' => 'bg-indigo-50 text-indigo-700',
    ];
    $previousDate = match($view) {
        'dia' => $selectedDate->copy()->subDay(),
        'semana' => $selectedDate->copy()->subWeek(),
        default => $selectedDate->copy()->subMonth(),
    };
    $nextDate = match($view) {
        'dia' => $selectedDate->copy()->addDay(),
        'semana' => $selectedDate->copy()->addWeek(),
        default => $selectedDate->copy()->addMonth(),
    };
    $periodTitle = match($view) {
        'dia' => ucfirst($selectedDate->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')),
        'semana' => $weekDays->first()['date']->locale('es')->isoFormat('D MMM').' — '.$weekDays->last()['date']->locale('es')->isoFormat('D MMM YYYY'),
        default => ucfirst($selectedDate->locale('es')->isoFormat('MMMM [de] YYYY')),
    };
@endphp

<div class="space-y-5">
    @if(session('access_denied'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">{{ session('access_denied') }}</div>
    @endif

    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#7065f0]">Vista general</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $isDoctor ? 'Resumen clínico' : 'Resumen de recepción' }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $isDoctor ? 'Consulta rápidamente tu agenda, pacientes y actividad clínica.' : 'Supervisa las citas, pacientes y tareas principales del consultorio.' }}</p>
        </div>
        <a href="{{ route('citas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200"><span class="text-lg leading-none">+</span>Nueva cita</a>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        @foreach([
            ['Citas de hoy', $resumen['citas_hoy'], 'Programadas para hoy', 'bg-indigo-50 text-indigo-600'],
            ['Pacientes', number_format($resumen['pacientes']), 'Expedientes activos', 'bg-emerald-50 text-emerald-600'],
            [$isDoctor ? 'Tratamientos pendientes' : 'Citas pendientes', $isDoctor ? $resumen['tratamientos_pendientes'] : $appointmentStatus['Pendiente'], 'Requieren seguimiento', 'bg-amber-50 text-amber-600'],
        ] as [$label, $value, $detail, $color])
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $color }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                <div><p class="text-xl font-bold text-slate-800">{{ $value }}</p><p class="text-xs font-semibold text-slate-600">{{ $label }}</p><p class="text-[10px] text-slate-400">{{ $detail }}</p></div>
            </article>
        @endforeach
    </section>

    <section>
        <div class="mb-3 flex items-center justify-between">
            <div><h2 class="text-sm font-bold text-slate-800">Acciones rápidas</h2><p class="text-[11px] text-slate-400">Accesos frecuentes</p></div>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                [route('pacientes.create'), 'Nuevo paciente', 'Crear expediente clínico', '＋', 'bg-[#eeecff] text-[#7065f0]'],
                [route('citas.create'), 'Agendar cita', 'Programar una consulta', '◷', 'bg-[#e9f8f4] text-[#29a88b]'],
                [$isDoctor ? route('pacientes.tratamiento') : route('inventario'), $isDoctor ? 'Nuevo tratamiento' : 'Ver inventario', $isDoctor ? 'Agregar al catálogo' : 'Revisar existencias', $isDoctor ? '✦' : '□', 'bg-[#fff5dc] text-[#d99512]'],
                [route('citas'), 'Gestionar citas', 'Consultar la agenda', '☷', 'bg-[#ffedf1] text-[#e85977]'],
            ] as [$url, $label, $detail, $icon, $style])
                <a href="{{ $url }}" class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 shadow-[0_4px_15px_rgba(30,34,60,.03)] transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-lg {{ $style }}">{{ $icon }}</span>
                    <span class="min-w-0"><strong class="block text-xs text-slate-700 group-hover:text-[#7065f0]">{{ $label }}</strong><small class="mt-0.5 block truncate text-[10px] text-slate-400">{{ $detail }}</small></span>
                    <span class="ml-auto text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-[#7065f0]">›</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_24px_rgba(30,34,60,.05)]">
        <header class="border-b border-slate-100 p-4 sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('inicio', ['view' => $view, 'date' => $previousDate->toDateString()]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50">‹</a>
                    <a href="{{ route('inicio', ['view' => $view, 'date' => now()->toDateString()]) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50">Hoy</a>
                    <a href="{{ route('inicio', ['view' => $view, 'date' => $nextDate->toDateString()]) }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50">›</a>
                    <h2 class="ml-2 hidden text-base font-bold text-slate-800 sm:block">{{ $periodTitle }}</h2>
                </div>
                <div class="flex rounded-xl bg-slate-100 p-1">
                    @foreach(['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'] as $key => $label)
                        <a href="{{ route('inicio', ['view' => $key, 'date' => $selectedDate->toDateString()]) }}" class="flex-1 rounded-lg px-4 py-2 text-center text-xs font-bold transition sm:flex-none {{ $view === $key ? 'bg-white text-[#7065f0] shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <h2 class="mt-3 text-sm font-bold text-slate-800 sm:hidden">{{ $periodTitle }}</h2>
        </header>

        @if($view === 'dia')
            <div class="p-4 sm:p-6">
                <div class="space-y-0">
                    @foreach(range(7, 20) as $hour)
                        @php $hourEvents = $selectedDayEvents->filter(fn ($event) => (int) date('G', strtotime($event['hora'])) === $hour); @endphp
                        <div class="grid min-h-20 grid-cols-[58px_1fr] border-b border-slate-100">
                            <div class="border-r border-slate-100 py-3 pr-3 text-right text-[11px] font-semibold text-slate-400">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00</div>
                            <div class="space-y-2 p-2">
                                @foreach($hourEvents as $event)
                                    <a href="{{ route('citas.show', $event['id']) }}" class="flex flex-col gap-2 rounded-xl border-l-4 border-[#7065f0] bg-[#f4f2ff] p-3 sm:flex-row sm:items-center">
                                        <strong class="text-xs text-[#554bc6]">{{ date('H:i', strtotime($event['hora'])) }}</strong>
                                        <span class="text-sm font-bold text-slate-700">{{ $event['paciente'] }}</span>
                                        <span class="text-xs text-slate-400">{{ $event['tipo'] }} · {{ $event['dentista'] }}</span>
                                        <span class="rounded-full px-2 py-1 text-[9px] font-bold sm:ml-auto {{ $statusStyles[$event['estado']] ?? 'bg-slate-100 text-slate-600' }}">{{ $event['estado'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($selectedDayEvents->isEmpty())<div class="pointer-events-none absolute inset-x-0 top-1/2 text-center text-sm text-slate-300">No hay citas para este día</div>@endif
            </div>
        @elseif($view === 'semana')
            <div class="overflow-x-auto">
                <div class="min-w-[900px]">
                    <div class="grid grid-cols-7 divide-x divide-slate-100 border-b border-slate-100 bg-white">
                        @foreach($weekDays as $day)
                            <div class="p-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider {{ $day['date']->isToday() ? 'text-[#7065f0]' : 'text-slate-400' }}">{{ $day['date']->locale('es')->isoFormat('ddd') }}</p>
                                <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $day['date']->isToday() ? 'bg-[#7065f0] text-white' : 'text-slate-700' }}">{{ $day['date']->format('d') }}</span>
                                <p class="mt-1 text-[9px] font-semibold text-slate-400">{{ $day['events']->count() }} {{ $day['events']->count() === 1 ? 'cita' : 'citas' }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 divide-x divide-slate-100">
                    @foreach($weekDays as $day)
                        <div class="min-h-[460px] bg-white p-2">
                            <div class="space-y-2">
                                @forelse($day['events'] as $event)
                                    <a href="{{ route('citas.show', $event['id']) }}" class="relative z-0 block min-h-[86px] w-full overflow-hidden rounded-xl border border-indigo-200 bg-[#f4f2ff] p-3 shadow-sm transition hover:border-indigo-400 hover:shadow-md">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="text-[11px] font-bold text-[#7065f0]">{{ date('H:i', strtotime($event['hora'])) }}</p>
                                            <span class="h-2 w-2 rounded-full bg-[#7065f0]"></span>
                                        </div>
                                        <p class="mt-2 truncate text-xs font-bold text-slate-800">{{ $event['paciente'] }}</p>
                                        <p class="mt-1 truncate text-[10px] text-slate-500">{{ $event['tipo'] }}</p>
                                        <p class="mt-1 truncate text-[9px] text-slate-400">{{ $event['dentista'] }}</p>
                                    </a>
                                @empty
                                    <p class="py-8 text-center text-[10px] text-slate-300">Sin citas</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="overflow-x-auto p-3 sm:p-5">
                <div class="min-w-[760px]">
                    <div class="grid grid-cols-7 border-b border-slate-100">
                        @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $weekday)<div class="py-3 text-center text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $weekday }}</div>@endforeach
                    </div>
                    <div class="grid grid-cols-7">
                        @foreach($calendarDays as $cell)
                            <div class="calendar-cell min-h-32 border-b border-r border-slate-100 p-2 {{ !$cell['day'] ? 'bg-slate-50/60' : 'bg-white' }}" @if($cell['date']) data-date="{{ $cell['date'] }}" @endif>
                                @if($cell['day'])
                                    <div class="mb-2 flex items-center justify-between"><span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $cell['date'] === now()->toDateString() ? 'bg-[#7065f0] text-white' : 'text-slate-600' }}">{{ $cell['day'] }}</span><span class="text-[9px] text-slate-300">{{ count($cell['events']) ?: '' }}</span></div>
                                    <div class="space-y-1">
                                        @foreach(array_slice($cell['events'], 0, 3) as $event)
                                            <a href="{{ route('citas.show', $event['id']) }}" class="draggable-event block truncate rounded-md bg-[#eeecff] px-2 py-1.5 text-[10px] font-semibold text-[#5a50cb]" draggable="true" data-event-id="{{ $event['id'] }}"><span class="font-bold">{{ date('H:i', strtotime($event['hora'])) }}</span> {{ $event['paciente'] }}</a>
                                        @endforeach
                                        @if(count($cell['events']) > 3)<span class="block px-2 text-[9px] font-semibold text-slate-400">+{{ count($cell['events']) - 3 }} citas más</span>@endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)] sm:p-6">
            <div class="flex items-center justify-between">
                <div><h2 class="font-bold text-slate-900">Actividad semanal</h2><p class="mt-0.5 text-xs text-slate-400">Citas programadas esta semana</p></div>
                <a href="{{ route('citas') }}" class="text-xs font-semibold text-[#7065f0]">Ver todas →</a>
            </div>
            <div class="mt-8 flex h-52 items-end gap-3 border-b border-slate-100 px-1 sm:gap-5">
                @foreach($weeklyAppointments as $day)
                    <div class="flex h-full flex-1 flex-col justify-end text-center">
                        <span class="mb-2 text-[10px] font-bold text-slate-400">{{ $day['count'] }}</span>
                        <div class="mx-auto w-full max-w-10 rounded-t-lg {{ $day['today'] ? 'bg-[#7065f0]' : 'bg-[#dcd9fb]' }}" style="height: {{ max(8, ($day['count'] / $maxWeeklyAppointments) * 155) }}px"></div>
                        <div class="mt-3 pb-3"><p class="text-[10px] font-semibold uppercase {{ $day['today'] ? 'text-[#7065f0]' : 'text-slate-400' }}">{{ $day['label'] }}</p><p class="text-xs font-bold text-slate-600">{{ $day['date'] }}</p></div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)] sm:p-6">
            <div><h2 class="font-bold text-slate-900">Estado de las citas</h2><p class="mt-0.5 text-xs text-slate-400">Distribución general</p></div>
            @php
                $confirmed = ($appointmentStatus['Confirmada'] / $totalAppointments) * 100;
                $pending = ($appointmentStatus['Pendiente'] / $totalAppointments) * 100;
                $attended = ($appointmentStatus['Asistida'] / $totalAppointments) * 100;
                $statusColors = ['Confirmada' => '#6f64e8', 'Pendiente' => '#f4b740', 'Asistida' => '#37b99b', 'Cancelada' => '#ef6a82'];
            @endphp
            <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row xl:flex-col 2xl:flex-row">
                <div class="relative h-36 w-36 shrink-0 rounded-full" style="background: conic-gradient(#6f64e8 0 {{ $confirmed }}%, #f4b740 {{ $confirmed }}% {{ $confirmed + $pending }}%, #37b99b {{ $confirmed + $pending }}% {{ $confirmed + $pending + $attended }}%, #ef6a82 {{ $confirmed + $pending + $attended }}% 100%)"><div class="absolute inset-[15px] flex flex-col items-center justify-center rounded-full bg-white"><strong class="text-2xl text-slate-900">{{ $appointmentStatus->sum() }}</strong><span class="text-[10px] text-slate-400">Total</span></div></div>
                <div class="w-full space-y-3">
                    @foreach($appointmentStatus as $status => $count)
                        <div class="flex items-center text-xs"><span class="mr-2 h-2.5 w-2.5 rounded-full" style="background: {{ $statusColors[$status] }}"></span><span class="text-slate-500">{{ $status }}</span><strong class="ml-auto text-slate-700">{{ $count }}</strong><span class="ml-2 w-8 text-right text-[10px] text-slate-400">{{ round(($count / $totalAppointments) * 100) }}%</span></div>
                    @endforeach
                </div>
            </div>
        </article>
    </section>

    <section class="grid gap-5 {{ $isDoctor ? '' : 'xl:grid-cols-[1.45fr_1fr]' }}">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div><h2 class="font-bold text-slate-900">Agenda de hoy</h2><p class="mt-0.5 text-xs text-slate-400">{{ $agendaHoy->count() }} citas programadas</p></div>
                <a href="{{ route('citas') }}" class="text-xs font-semibold text-[#7065f0]">Ver todas →</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($agendaHoy->take(5) as $cita)
                    <div class="flex items-center gap-3 px-5 py-4 sm:px-6">
                        <div class="w-12 shrink-0 text-center"><p class="text-sm font-bold text-slate-800">{{ date('H:i', strtotime($cita['hora'])) }}</p><p class="text-[9px] uppercase text-slate-400">hora</p></div>
                        <div class="h-9 w-px bg-slate-100"></div>
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#eeecff] text-xs font-bold text-[#7065f0]">{{ mb_strtoupper(mb_substr($cita['paciente'], 0, 1)) }}</div>
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-slate-800">{{ $cita['paciente'] }}</p><p class="truncate text-[11px] text-slate-400">{{ $cita['tipo'] }} · {{ $cita['dentista'] }}</p></div>
                        <span class="hidden rounded-full px-2.5 py-1 text-[10px] font-bold sm:inline-flex {{ $statusStyles[$cita['estado']] ?? 'bg-slate-50 text-slate-600' }}">{{ $cita['estado'] }}</span>
                        <a href="{{ route('citas.show', $cita['id']) }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-[#7065f0]">•••</a>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center"><p class="text-sm font-medium text-slate-500">No hay citas programadas para hoy</p><a href="{{ route('citas.create') }}" class="mt-2 inline-block text-xs font-semibold text-[#7065f0]">Agendar una cita</a></div>
                @endforelse
            </div>
        </article>

        <div class="space-y-5">
            @if(!$isDoctor)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
                    <div class="flex items-center justify-between"><div><h2 class="font-bold text-slate-900">Alertas de inventario</h2><p class="mt-0.5 text-xs text-slate-400">Productos por reponer</p></div><a href="{{ route('inventario') }}" class="text-xs font-semibold text-[#7065f0]">Ver todo</a></div>
                    <div class="mt-4 space-y-3">
                        @forelse($inventoryAlerts as $item)
                            <div class="flex items-center gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-xs font-bold text-rose-500">!</span><div class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-slate-700">{{ $item->material }}</p><p class="text-[10px] text-slate-400">Mínimo: {{ $item->reposicion }}</p></div><span class="rounded-full bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-600">{{ $item->stock }} uds.</span></div>
                        @empty<div class="rounded-xl bg-emerald-50 p-3 text-xs font-medium text-emerald-700">Inventario con existencias suficientes.</div>@endforelse
                    </div>
                </article>
            @endif
        </div>
    </section>

    <section class="grid gap-5 {{ $isDoctor ? 'lg:grid-cols-2' : '' }}">
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-bold text-slate-900">Próximas citas</h2><p class="text-xs text-slate-400">Seguimiento de agenda</p></div><a href="{{ route('citas') }}" class="text-xs font-semibold text-[#7065f0]">Ver agenda</a></div>
            <div class="divide-y divide-slate-100">
                @forelse($proximasCitas as $cita)
                    <div class="flex items-center gap-3 px-5 py-3.5"><div class="w-11 rounded-lg bg-slate-50 py-1.5 text-center"><strong class="block text-sm text-slate-700">{{ date('d', strtotime($cita['fecha'])) }}</strong><span class="text-[8px] uppercase text-slate-400">{{ date('M', strtotime($cita['fecha'])) }}</span></div><div class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-slate-700">{{ $cita['paciente'] }}</p><p class="truncate text-[10px] text-slate-400">{{ date('H:i', strtotime($cita['hora'])) }} · {{ $cita['tipo'] }}</p></div><span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $statusStyles[$cita['estado']] ?? 'bg-slate-50 text-slate-600' }}">{{ $cita['estado'] }}</span></div>
                @empty<div class="p-8 text-center text-xs text-slate-400">No hay próximas citas.</div>@endforelse
            </div>
        </article>

        @if($isDoctor)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_4px_20px_rgba(30,34,60,.04)]">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="font-bold text-slate-900">Tratamientos recientes</h2><p class="text-xs text-slate-400">Actividad clínica</p></div><a href="{{ route('pacientes.tratamientos') }}" class="text-xs font-semibold text-[#7065f0]">Ver todos</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse($tratamientos as $tratamiento)
                        <div class="flex items-center gap-3 px-5 py-3.5"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#eeecff] text-[#7065f0]">✦</span><div class="min-w-0 flex-1"><p class="truncate text-xs font-semibold text-slate-700">{{ $tratamiento['tratamiento'] }}</p><p class="truncate text-[10px] text-slate-400">{{ $tratamiento['paciente'] }} · {{ $tratamiento['tipo'] }}</p></div><span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $statusStyles[$tratamiento['estado']] ?? 'bg-amber-50 text-amber-700' }}">{{ $tratamiento['estado'] }}</span></div>
                    @empty<div class="p-8 text-center text-xs text-slate-400">No hay tratamientos recientes.</div>@endforelse
                </div>
            </article>
        @endif
    </section>
</div>
@endsection
