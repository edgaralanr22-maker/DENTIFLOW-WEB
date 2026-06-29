@extends('layouts.app')

@section('title', 'Crear material de inventario')

@section('content')

<div>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Agregar material al inventario</h1>
            <p class="text-gray-500 mt-2">Registra un nuevo material dental con su stock, costo y proveedor.</p>
        </div>
        <a href="{{ route('inventario') }}" class="bg-gray-200 text-gray-800 px-6 py-3 rounded-2xl hover:bg-gray-300">Volver al inventario</a>
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

        <form action="{{ route('inventario.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Material</span>
                    <input type="text" name="material" value="{{ old('material') }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
                <label class="block">
                    <span class="text-gray-700">Proveedor</span>
                    <input type="text" name="proveedor" value="{{ old('proveedor') }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <label class="block">
                    <span class="text-gray-700">Stock</span>
                    <input type="number" name="stock" value="{{ old('stock') }}" min="0" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
                <label class="block">
                    <span class="text-gray-700">Nivel de reposición</span>
                    <input type="number" name="reposicion" value="{{ old('reposicion') }}" min="0" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
                <label class="block">
                    <span class="text-gray-700">Costo unitario</span>
                    <input type="number" step="0.01" name="costo" value="{{ old('costo') }}" min="0" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-[#4B136B] text-white px-8 py-3 rounded-2xl hover:bg-purple-800">Guardar material</button>
            </div>
        </form>
    </div>
</div>

@endsection