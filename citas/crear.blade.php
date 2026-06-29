@extends('layouts.app')

@section('title','Crear Cita')

@section('content')

<div>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-4xl font-bold text-gray-800">
                Agendar nueva cita
            </h1>

            <p class="text-gray-500 mt-2">
                Complete el formulario para registrar una cita.
            </p>

        </div>

        <a href="{{ route('citas') }}" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-2xl hover:bg-gray-300">
            Volver
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

        <form action="{{ route('citas.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Paciente</span>
                    <select name="paciente" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                        <option value="">Seleccione un paciente</option>
                        @foreach($pacientes as $paciente)
                            <option value="{{ $paciente }}" {{ old('paciente') === $paciente ? 'selected' : '' }}>{{ $paciente }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-gray-700">Dentista</span>
                    @if($currentDentist)
                        <input type="hidden" name="dentista" value="{{ $currentDentist }}">
                        <div class="mt-2 flex w-full items-center gap-3 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-indigo-800">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold">{{ mb_strtoupper(mb_substr($currentDentist, 0, 1)) }}</span>
                            <span class="font-semibold">{{ $currentDentist }}</span>
                            <span class="ml-auto text-xs text-indigo-500">Sesión actual</span>
                        </div>
                    @else
                        <select name="dentista" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                            <option value="">Seleccione un dentista</option>
                            @foreach($dentistas as $dentista)
                                <option value="{{ $dentista }}" {{ old('dentista') === $dentista ? 'selected' : '' }}>{{ $dentista }}</option>
                            @endforeach
                        </select>
                    @endif
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <label class="block">
                    <span class="text-gray-700">Fecha</span>
                    <input type="date" name="fecha" value="{{ old('fecha') }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>

                <label class="block">
                    <span class="text-gray-700">Hora</span>
                    <input type="time" name="hora" value="{{ old('hora') }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>

                <label class="block">
                    <span class="text-gray-700">Tratamiento</span>
                    <select name="tipo" required class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                        <option value="">Seleccione un tratamiento</option>
                        @foreach($tratamientos as $tratamiento)
                            <option value="{{ $tratamiento->tratamiento }}" @selected(old('tipo') === $tratamiento->tratamiento)>
                                {{ $tratamiento->tratamiento }} · {{ $tratamiento->tipo }} · ${{ number_format($tratamiento->costo, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @if($tratamientos->isEmpty())
                        <p class="mt-2 text-xs text-amber-600">No hay tratamientos registrados en el catálogo.</p>
                    @endif
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-[#4B136B] text-white px-8 py-3 rounded-2xl hover:bg-purple-800">
                    Guardar cita
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
