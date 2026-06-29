@extends('layouts.app')

@section('title', 'Inventario')

@section('content')

<div>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-gray-800">Inventario</h1>
            <p class="text-gray-500 mt-2">Control de stock, alertas de reposición y costos por proveedor.</p>
        </div>
        <a href="{{ route('pacientes') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#4B136B] px-6 py-3 text-white hover:bg-purple-800">Volver al panel</a>
    </div>

    <div class="grid gap-6 md:grid-cols-3 mb-6">
        <div class="rounded-3xl bg-white p-6 shadow-lg">
            <p class="text-sm text-gray-500">Productos en alerta</p>
            <p class="mt-4 text-4xl font-bold text-[#4B136B]">{{ $alertas->count() }}</p>
            <p class="mt-2 text-sm text-gray-500">Materiales que requieren reposición inmediata.</p>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg">
            <p class="text-sm text-gray-500">Proveedores activos</p>
            <p class="mt-4 text-4xl font-bold text-[#4B136B]">{{ $costosPorProveedor->count() }}</p>
            <p class="mt-2 text-sm text-gray-500">Número de proveedores registrados en inventario.</p>
        </div>
        <div class="rounded-3xl bg-white p-6 shadow-lg">
            <p class="text-sm text-gray-500">Stock total estimado</p>
            <p class="mt-4 text-4xl font-bold text-[#4B136B]">{{ $productos->sum('stock') }}</p>
            <p class="mt-2 text-sm text-gray-500">Unidades disponibles en stock.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-lg p-6 mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Stock de materiales dentales</h2>
                <p class="text-sm text-gray-500">Revisa el estado actual de los materiales y sus proveedores.</p>
            </div>
            <a href="{{ route('inventario.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-[#4B136B] px-5 py-3 text-white hover:bg-purple-800">Agregar material</a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-3xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#4B136B] text-white">
                    <tr>
                        <th class="px-6 py-4">Material</th>
                        <th class="px-6 py-4">Stock disponible</th>
                        <th class="px-6 py-4">Nivel de reposición</th>
                        <th class="px-6 py-4">Costo unitario</th>
                        <th class="px-6 py-4">Proveedor</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-800">{{ $producto['material'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $producto['stock'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $producto['reposicion'] }}</td>
                            <td class="px-6 py-4 text-gray-700">${{ number_format($producto['costo'], 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $producto['proveedor'] }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex flex-col items-end gap-2 md:flex-row md:justify-end">
                                    <a href="{{ route('inventario.edit', ['id' => $producto['id']]) }}" class="text-indigo-600 hover:underline">Editar</a>
                                    <a href="{{ route('inventario.delete', ['id' => $producto['id']]) }}" class="text-red-600 hover:underline">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-3xl shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Alertas de reposición</h2>
            <div class="space-y-4">
                @forelse($alertas as $item)
                    <div class="rounded-3xl border border-red-100 bg-red-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $item['material'] }}</p>
                                <p class="text-sm text-gray-500">Proveedor: {{ $item['proveedor'] }}</p>
                            </div>
                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">Stock bajo</span>
                        </div>
                        <p class="mt-3 text-gray-700">Cantidad disponible: {{ $item['stock'] }} · Nivel objetivo: {{ $item['reposicion'] }}</p>
                    </div>
                @empty
                    <div class="rounded-3xl border border-gray-200 p-4 text-gray-500">No hay alertas críticas en este momento.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Costos por proveedor</h2>
            <div class="space-y-4">
                @foreach($costosPorProveedor as $proveedor => $info)
                    <div class="rounded-3xl border border-gray-200 p-4">
                        <p class="font-semibold text-gray-800">{{ $proveedor }}</p>
                        <p class="text-sm text-gray-500">Materiales: {{ $info['total_materiales'] }}</p>
                        <p class="mt-2 text-gray-700">Costo promedio: ${{ number_format($info['costo_promedio'], 2, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection