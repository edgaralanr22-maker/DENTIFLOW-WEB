@extends('layouts.app')

@section('title', 'Historial de Pacientes')

@section('content')

<div>

    <div class="mb-8">

        <h1 class="text-4xl font-bold text-gray-800">
            Historial de Pacientes
        </h1>

        <p class="text-gray-500 mt-2">
            Registros recientes de citas y actividades de los pacientes.
        </p>

    </div>

    <div class="grid gap-6 lg:grid-cols-3 mb-8">
        <div class="bg-white rounded-3xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Total de pacientes</p>
            <p class="mt-4 text-4xl font-bold text-[#4B136B]">{{ $totalPacientes }}</p>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Total de dinero</p>
            <p class="mt-4 text-4xl font-bold text-[#4B136B]">${{ number_format($totalDinero, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6">
            <p class="text-sm text-gray-500">Tratamientos</p>
            <ul class="mt-4 space-y-2 text-gray-700">
                @foreach($tratamientos as $tratamiento => $cantidad)
                    <li class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-3">
                        <span>{{ $tratamiento }}</span>
                        <span class="font-semibold text-[#4B136B]">{{ $cantidad }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg overflow-hidden">

        <table class="w-full">

            <thead class="bg-[#4B136B] text-white">
                <tr>
                    <th class="px-6 py-4 text-left">Paciente</th>
                    <th class="px-6 py-4 text-left">Actividad</th>
                    <th class="px-6 py-4 text-left">Fecha</th>
                    <th class="px-6 py-4 text-left">Estado</th>
                </tr>
            </thead>

            <tbody>
                @forelse($historial as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-700">{{ $item['paciente'] }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item['tratamiento'] }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $item['fecha'] }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            <span class="{{ $item['estado'] === 'Cancelada' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} px-3 py-1 rounded-full">
                                {{ $item['estado'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-10 text-center text-gray-500" colspan="4">
                            No hay historial disponible.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection