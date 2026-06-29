@extends('layouts.app')

@section('title', 'Editar material de inventario')

@section('content')

<div>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Editar material de inventario</h1>
            <p class="text-gray-500 mt-2">Modifica los datos del material y guarda los cambios.</p>
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

        <form action="{{ route('inventario.update', ['id' => $item['id']]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid gap-6 md:grid-cols-2">
                <label class="block">
                    <span class="text-gray-700">Material</span>
                    <input type="text" name="material" value="{{ old('material', $item['material']) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
                <label class="block">
                    <span class="text-gray-700">Proveedor</span>
                    <input type="text" name="proveedor" value="{{ old('proveedor', $item['proveedor']) }}" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <label class="block">
                    <span class="text-gray-700">Stock</span>
                    <input type="number" name="stock" value="{{ old('stock', $item['stock']) }}" min="0" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
                <label class="block">
                    <span class="text-gray-700">Nivel de reposición</span>
                    <input type="number" name="reposicion" value="{{ old('reposicion', $item['reposicion']) }}" min="0" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
                <label class="block">
                    <span class="text-gray-700">Costo unitario</span>
                    <input type="number" step="0.01" name="costo" value="{{ old('costo', $item['costo']) }}" min="0" class="mt-2 w-full rounded-2xl border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-purple-300">
                </label>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('inventario') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 bg-white px-6 py-3 text-gray-700 hover:bg-gray-100">Cancelar</a>
                <button type="submit" class="bg-[#4B136B] text-white px-8 py-3 rounded-2xl hover:bg-purple-800">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

@endsection