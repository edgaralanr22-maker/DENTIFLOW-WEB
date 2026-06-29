@extends('layouts.app')

@section('title','Reportes')

@section('content')

@php
    $periodoOptions = [
        'Este mes' => 'Este mes',
        'Últimos 3 meses' => 'Últimos 3 meses',
        'Últimos 6 meses' => 'Últimos 6 meses',
        'Este año' => 'Este año',
        'Personalizado' => 'Personalizado',
    ];

    $periodoActual = request('periodo', 'Últimos 6 meses');
    $periodoLabel = $periodoOptions[$periodoActual] ?? 'Últimos 6 meses';

    $metricas = [
        [
            'key' => 'ingresos',
            'detail' => 'Ingresos del periodo',
            'tone' => 'border-sky-200 bg-sky-50 text-sky-700',
        ],
        [
            'key' => 'pacientes',
            'detail' => 'Pacientes atendidos',
            'tone' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        ],
        [
            'key' => 'citas',
            'detail' => 'Citas registradas',
            'tone' => 'border-purple-200 bg-purple-50 text-[#4B136B]',
        ],
        [
            'key' => 'tratamientos',
            'detail' => 'Tratamientos activos',
            'tone' => 'border-amber-200 bg-amber-50 text-amber-700',
        ],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#4B136B]/70">Analitica clinica</p>
            <h1 class="mt-2 text-4xl font-bold text-slate-900">Reportes</h1>
            <p class="mt-2 max-w-2xl text-slate-500">
                Revisa ingresos, citas, pacientes y desempeno clinico del consultorio.
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('pacientes.historial') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Historial
            </a>
            <a href="{{ route('reportes.export', array_merge(request()->query(), ['type' => 'pdf'])) }}" class="inline-flex items-center justify-center rounded-lg border border-[#4B136B]/30 bg-white px-5 py-3 text-sm font-semibold text-[#4B136B] shadow-sm transition hover:bg-purple-50">
                Exportar PDF
            </a>
            <a href="{{ route('reportes.export', array_merge(request()->query(), ['type' => 'excel'])) }}" class="inline-flex items-center justify-center rounded-lg bg-[#4B136B] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-800">
                Exportar Excel
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('reportes') }}" method="GET" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-[minmax(180px,0.8fr)_minmax(150px,0.7fr)_minmax(150px,0.7fr)_auto] lg:items-end">
            <label class="block">
                <span class="text-sm font-medium text-slate-600">Periodo</span>
                <select id="periodo" name="periodo" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-700 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
                    @foreach($periodoOptions as $value => $label)
                        <option value="{{ $value }}" {{ $periodoActual === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Mes inicial</span>
                <input
                    id="desde"
                    name="desde"
                    type="month"
                    value="{{ request('desde') }}"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-600">Mes final</span>
                <input
                    id="hasta"
                    name="hasta"
                    type="month"
                    value="{{ request('hasta') }}"
                    class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-[#4B136B] focus:ring-2 focus:ring-[#4B136B]/20">
            </label>

            <div class="flex gap-3">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-[#4B136B] px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800 lg:flex-none">
                    Aplicar
                </button>

                @if(count(request()->query()) > 0)
                    <a href="{{ route('reportes') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Limpiar
                    </a>
                @endif
            </div>
        </div>

        <p class="mt-4 text-sm text-slate-500">
            Periodo actual: <span class="font-semibold text-slate-700">{{ $periodoLabel }}</span>
            @if($periodoActual === 'Personalizado' && preg_match('/^\d{4}-\d{2}$/', request('desde', '')) && preg_match('/^\d{4}-\d{2}$/', request('hasta', '')))
                <span> · {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', request('desde'))->locale('es')->translatedFormat('F Y') }} a {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', request('hasta'))->locale('es')->translatedFormat('F Y') }}</span>
            @endif
        </p>
    </form>

    @if(! $hasData)
        <div class="rounded-lg border border-slate-200 bg-white px-5 py-12 text-center shadow-sm">
            <p class="text-lg font-semibold text-slate-900">No hay datos suficientes para este periodo.</p>
            <p class="mt-2 text-sm text-slate-500">Ajusta el periodo o selecciona un rango personalizado valido.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($metricas as $metrica)
                @php
                    $key = $metrica['key'];
                    $trend = $trends[$key];
                    $isPositive = $trend['positive'];
                @endphp
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $trend['label'] }}</p>
                            <p class="mt-3 text-3xl font-bold text-slate-950">{{ $reportes[$key]['display'] }}</p>
                        </div>
                        <span class="h-3 w-3 rounded-full border {{ $metrica['tone'] }}"></span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm">
                        <span class="font-semibold {{ $isPositive ? 'text-emerald-700' : 'text-rose-700' }}">{{ $isPositive ? '+' : '-' }}{{ $trend['percent'] }}%</span>
                        <span class="text-slate-500">vs periodo anterior</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">{{ $metrica['detail'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Distribucion de tratamientos</h2>
                    <p class="text-sm text-slate-500">Porcentaje de citas por tipo de tratamiento.</p>
                </div>

                <div class="space-y-5 p-5">
                    @foreach($distribution as $item)
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $item['tratamiento'] }}</span>
                                <span class="font-semibold text-slate-900">{{ $item['porcentaje'] }}%</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-[#4B136B]" style="width: {{ $item['porcentaje'] }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Top dentistas</h2>
                    <p class="text-sm text-slate-500">Ordenado por cantidad de citas.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Dentista</th>
                                <th class="px-5 py-4 font-semibold">Citas</th>
                                <th class="px-5 py-4 text-right font-semibold">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($topDentistas as $dentista)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-4 font-medium text-slate-800">{{ $dentista['dentista'] }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $dentista['citas'] }}</td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-900">${{ number_format($dentista['ingresos'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Ingresos por mes</h2>
                    <p class="text-sm text-slate-500">Comparativo de los ultimos meses disponibles.</p>
                </div>

                <div class="space-y-4 p-5">
                    @foreach($grafico as $punto)
                        <div class="grid grid-cols-[52px_1fr_96px] items-center gap-4">
                            <span class="text-sm font-medium text-slate-500">{{ $punto['mes'] }}</span>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-sky-600" style="width: {{ min(max(($punto['valor'] / 13000) * 100, 5), 100) }}%;"></div>
                            </div>
                            <span class="text-right text-sm font-semibold text-slate-800">${{ number_format($punto['valor'], 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Lectura rapida</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Ingresos promedio por cita</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">$295</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Crecimiento mensual de citas</p>
                        <p class="mt-1 text-xl font-bold text-emerald-700">+12%</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Tratamientos activos</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">{{ $reportes['tratamientos']['display'] ?? $reportes['tratamientos'] }}</p>
                    </div>
                </div>
            </section>
        </div>
    @endif
</div>

@endsection
