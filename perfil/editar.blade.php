@extends('layouts.app')

@section('title','Editar Perfil')

@section('content')

<div>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-4xl font-bold text-gray-800">
                Editar Perfil
            </h1>

            <p class="text-gray-500 mt-2">
                Actualiza tus datos de contacto y tu especialidad.
            </p>

        </div>

        <a href="{{ route('perfil') }}" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-2xl hover:bg-gray-300">
            Volver al perfil
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

        <form action="{{ route('perfil.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Nombre</span>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario['nombre']) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>

                <label class="block">
                    <span class="text-gray-700">Email</span>
                    <input type="email" name="email" value="{{ old('email', $usuario['email']) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Teléfono</span>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario['telefono']) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>

                <label class="block">
                    <span class="text-gray-700">Especialidad</span>
                    <input type="text" name="especialidad" value="{{ old('especialidad', $usuario['especialidad']) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-[#4B136B] text-white px-8 py-3 rounded-2xl hover:bg-purple-800">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>

</div>

@endsection