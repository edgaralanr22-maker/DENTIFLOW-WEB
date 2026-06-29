@extends('layouts.app')

@section('title','Detalle de Cita')

@section('content')

<div>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-4xl font-bold text-gray-800">
                Detalle de cita
            </h1>

            <p class="text-gray-500 mt-2">
                Revisa información completa de la cita seleccionada.
            </p>

        </div>

        <a href="{{ route('citas') }}" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-2xl hover:bg-gray-300">
            Volver a citas
        </a>

    </div>

    <div class="bg-white rounded-3xl shadow-lg p-8">

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-3xl bg-gray-50 p-6">
                <p class="text-sm text-gray-500">Paciente</p>
                <p class="mt-3 text-2xl font-semibold text-gray-800">{{ $cita['paciente'] }}</p>
            </div>
            <div class="rounded-3xl bg-gray-50 p-6">
                <p class="text-sm text-gray-500">Dentista</p>
                <p class="mt-3 text-2xl font-semibold text-gray-800">{{ $cita['dentista'] }}</p>
            </div>
            <div class="rounded-3xl bg-gray-50 p-6">
                <p class="text-sm text-gray-500">Fecha</p>
                <p class="mt-3 text-2xl font-semibold text-gray-800">{{ date('d/m/Y', strtotime($cita['fecha'])) }}</p>
            </div>
            <div class="rounded-3xl bg-gray-50 p-6">
                <p class="text-sm text-gray-500">Hora</p>
                <p class="mt-3 text-2xl font-semibold text-gray-800">{{ $cita['hora'] }}</p>
            </div>
            <div class="rounded-3xl bg-gray-50 p-6">
                <p class="text-sm text-gray-500">Tipo de servicio</p>
                <p class="mt-3 text-2xl font-semibold text-gray-800">{{ $cita['tipo'] }}</p>
            </div>
            <div class="rounded-3xl bg-gray-50 p-6">
                <p class="text-sm text-gray-500">Estado</p>
                <p class="mt-3 text-2xl font-semibold text-gray-800">{{ $cita['estado'] }}</p>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-end">
            <a href="{{ route('citas.edit', ['id' => $cita['id']]) }}" class="inline-flex items-center justify-center rounded-2xl border border-[#4B136B] px-6 py-3 text-[#4B136B] hover:bg-purple-50">
                Editar cita
            </a>
            <a href="{{ route('citas.confirmar', ['id' => $cita['id']]) }}" class="inline-flex items-center justify-center rounded-2xl bg-green-100 px-6 py-3 text-green-700 hover:bg-green-200">
                Confirmar
            </a>
            <a href="{{ route('citas.cancelar', ['id' => $cita['id']]) }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-100 px-6 py-3 text-orange-700 hover:bg-orange-200">
                Cancelar
            </a>
        </div>

    </div>

</div>

@endsection