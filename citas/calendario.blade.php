@extends('layouts.app')

@section('title','Calendario')

@section('content')

<div class="space-y-6">
    <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#7065f0]">Agenda clínica</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Calendario</h1>
            <p class="mt-2 text-sm text-slate-500">Organiza y consulta todas las citas del consultorio por día, semana o mes.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex rounded-xl bg-slate-200/70 p-1">
                @foreach(['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'] as $key => $label)
                    <a href="{{ route('calendario', array_merge(request()->query(), ['view' => $key])) }}" class="flex-1 rounded-lg px-4 py-2 text-center text-xs font-bold transition sm:flex-none {{ request('view', 'mes') === $key ? 'bg-white text-[#7065f0] shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">{{ $label }}</a>
                @endforeach
            </div>
            <a href="{{ route('citas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#7065f0] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:bg-[#5e54dd]"><span class="text-lg leading-none">+</span>Nueva cita</a>
        </div>
    </header>

    <section class="grid gap-4 lg:grid-cols-[1.5fr_1fr]">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-[#7065f0]"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></span>
                    <div><h2 class="text-sm font-bold text-slate-800">Recordatorios automáticos</h2><p class="mt-1 text-xs text-slate-400">Mantén informados a tus pacientes antes de cada cita.</p></div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach([['SMS','bg-sky-50 text-sky-600'],['WhatsApp','bg-emerald-50 text-emerald-600'],['Email','bg-violet-50 text-violet-600']] as [$channel,$style])
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-bold {{ $style }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $channel }}</span>
                    @endforeach
                </div>
            </div>
        </article>
        <article class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_4px_20px_rgba(30,34,60,.04)]">
            <a href="{{ route('citas.create') }}" class="flex flex-1 items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50/60 p-3 transition hover:border-indigo-200"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#7065f0] text-white">＋</span><span><strong class="block text-xs text-slate-700">Agendar cita</strong><small class="text-[10px] text-slate-400">Nuevo registro</small></span></a>
            <a href="{{ route('citas') }}" class="flex flex-1 items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 transition hover:border-slate-200"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-slate-500">☷</span><span><strong class="block text-xs text-slate-700">Ver citas</strong><small class="text-[10px] text-slate-400">Lista completa</small></span></a>
        </article>
    </section>

    @if(request('view', 'mes') === 'dia')
        <div class="bg-white rounded-3xl shadow-lg p-6 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <p class="text-lg font-semibold text-gray-700">Vista Día</p>
                    <p class="text-sm text-gray-500">Agenda del día con bloques horarios y recordatorios.</p>
                </div>
                <a href="{{ route('citas.create') }}" class="rounded-2xl bg-[#4B136B] px-4 py-2 text-white hover:bg-purple-800">Nueva cita</a>
            </div>

            <div class="grid gap-4">
                @forelse($todayEvents as $event)
                    <div class="rounded-3xl border border-gray-200 p-6">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm text-gray-500">{{ date('d/m/Y', strtotime($event['fecha'])) }}</p>
                                <h2 class="text-xl font-semibold text-gray-800">{{ $event['paciente'] }}</h2>
                                <p class="text-gray-500">{{ $event['tipo'] }} · {{ $event['dentista'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-[#4B136B]">{{ $event['hora'] }}</p>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">{{ $event['estado'] ?? 'Programada' }}</span>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3 text-sm">
                            <a href="{{ route('citas.edit', ['id' => $event['id']]) }}" class="text-[#4B136B] hover:underline">Editar</a>
                            <a href="{{ route('citas.reprogramar', ['id' => $event['id']]) }}" class="text-yellow-600 hover:underline">Reprogramar</a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-gray-200 p-6 text-gray-500">No hay citas para hoy.</div>
                @endforelse
            </div>
        </div>
    @elseif(request('view', 'mes') === 'semana')
        @php
            $weekGrouped = collect($weekEvents)->groupBy(fn($event) => date('N', strtotime($event['fecha'])));
        @endphp
        <div class="bg-white rounded-3xl shadow-lg p-6 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <p class="text-lg font-semibold text-gray-700">Vista Semana</p>
                    <p class="text-sm text-gray-500">Resumen de la semana actual con citas agrupadas por día.</p>
                </div>
                <a href="{{ route('citas.create') }}" class="rounded-2xl bg-[#4B136B] px-4 py-2 text-white hover:bg-purple-800">Nueva cita</a>
            </div>

            <div class="grid gap-4">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $index => $day)
                    <div class="rounded-3xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-semibold text-gray-700">{{ $day }}</p>
                            <span class="text-sm text-gray-500">{{ $weekGrouped->has($index + 1) ? $weekGrouped[$index + 1]->count() . ' citas' : 'Sin citas' }}</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($weekGrouped[$index + 1] ?? collect() as $event)
                                <div class="rounded-2xl bg-[#f6f0ff] p-3">
                                    <p class="text-sm font-semibold text-gray-800">{{ $event['hora'] }} · {{ $event['paciente'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $event['tipo'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if(request('view', 'mes') === 'mes')
        <div class="bg-white rounded-3xl shadow-lg p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
                <div>
                    <p class="text-lg font-semibold text-gray-700">Vista Mes</p>
                    <p class="text-sm text-gray-500">Mira la distribución mensual de tu agenda y arrastra citas entre días.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('calendario', ['year' => $prevMonth->format('Y'), 'month' => $prevMonth->format('n'), 'view' => request('view', 'mes')]) }}" class="rounded-2xl border border-gray-200 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50">Anterior</a>
                    <a href="{{ route('calendario', ['year' => $nextMonth->format('Y'), 'month' => $nextMonth->format('n'), 'view' => request('view', 'mes')]) }}" class="rounded-2xl bg-[#4B136B] px-4 py-2 text-white hover:bg-purple-800">Siguiente</a>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-4 text-center text-sm font-semibold text-gray-600 mb-4">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $weekday)
                    <div>{{ $weekday }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-4 text-sm">
                @foreach($calendarDays as $cell)
                    <div class="calendar-cell min-h-[120px] rounded-3xl border p-3 shadow-sm {{ $cell['day'] ? 'border-gray-200 bg-white' : 'border-transparent bg-transparent' }}" data-date="{{ $cell['date'] }}">
                        @if($cell['day'])
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-lg font-semibold text-gray-800">{{ $cell['day'] }}</span>
                                @if($cell['date'] === $today->format('Y-m-d'))
                                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs text-purple-700">Hoy</span>
                                @endif
                            </div>

                            @foreach($cell['events'] as $evento)
                                <div class="draggable-event mb-2 rounded-2xl bg-[#4B136B] px-2 py-2 text-left text-xs text-white cursor-grab" draggable="true" data-event-id="{{ $evento['id'] }}">
                                    <div class="font-semibold">{{ $evento['hora'] }}</div>
                                    <div>{{ substr($evento['paciente'], 0, 18) }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@endsection
