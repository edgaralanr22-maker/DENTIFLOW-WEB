@extends('layouts.app')

@section('title','Editar Cita')

@section('content')

<div>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-4xl font-bold text-gray-800">
                Editar cita
            </h1>

            <p class="text-gray-500 mt-2">
                Ajusta los detalles de la cita y guarda los cambios.
            </p>

        </div>

        <a href="{{ route('citas') }}" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-2xl hover:bg-gray-300">
            Volver a citas
        </a>

    </div>

    <div class="bg-white rounded-3xl shadow-lg p-8">

        @if ($errors->any())
            <div class="mb-6 rounded-3xl bg-red-50 border border-red-200 p-4 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('citas.update', ['id' => $cita['id']]) }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Paciente</span>
                    <select name="paciente" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                        <option value="">Seleccione un paciente</option>
                        @foreach($pacientes as $paciente)
                            <option value="{{ $paciente }}" {{ old('paciente', $cita['paciente']) === $paciente ? 'selected' : '' }}>{{ $paciente }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-gray-700">Dentista</span>
                    <select name="dentista" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                        <option value="">Seleccione un dentista</option>
                        @foreach($dentistas as $dentista)
                            <option value="{{ $dentista }}" {{ old('dentista', $cita['dentista']) === $dentista ? 'selected' : '' }}>{{ $dentista }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <label class="block">
                    <span class="text-gray-700">Fecha</span>
                    <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d', strtotime($cita['fecha']))) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>

                <label class="block">
                    <span class="text-gray-700">Hora</span>
                    <input type="time" name="hora" value="{{ old('hora', date('H:i', strtotime($cita['hora']))) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>

                <label class="block">
                    <span class="text-gray-700">Tipo de servicio</span>
                    <input type="text" name="tipo" value="{{ old('tipo', $cita['tipo']) }}" placeholder="Ej. Limpieza Dental" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Estado</span>
                    <select name="estado" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                        @foreach(['Pendiente', 'Confirmada', 'Cancelada'] as $estado)
                            <option value="{{ $estado }}" {{ old('estado', $cita['estado']) === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="block">
                    <span class="text-gray-700">ID de cita</span>
                    <div class="mt-2 rounded-2xl border border-gray-300 bg-gray-50 px-4 py-3 text-gray-700">{{ $cita['id'] }}</div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('citas') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-6 py-3 text-gray-700 hover:bg-gray-100">
                    Cancelar
                </a>
                <button type="submit" class="bg-[#4B136B] text-white px-8 py-3 rounded-2xl hover:bg-purple-800">
                    Guardar cambios
                </button>
            </div>
        </form>

    </div>

</div>

@endsection