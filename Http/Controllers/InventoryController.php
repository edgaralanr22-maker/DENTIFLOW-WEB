<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index()
    {
        $productos = InventoryItem::orderBy('material')->get();

        $alertas = $productos->filter(fn($item) => $item->needsRestock())->values();
        $costoTotalInventario = $productos->sum(
            fn ($item) => $item->inventoryValue()
        );
        $costosPorProveedor = $productos
            ->groupBy(fn ($item) => trim((string) $item->proveedor) ?: 'Sin proveedor')
            ->map(fn ($items) => [
                'total_materiales' => $items->count(),
                'costo_promedio' => $items->avg(fn ($item) => (float) $item->costo) ?? 0,
            ]);

        return view('inventario.index', compact('productos', 'alertas', 'costoTotalInventario', 'costosPorProveedor'));
    }

    public function create()
    {
        return view('inventario.crear');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material' => 'required|string|max:255|unique:inventory_items,material',
            'stock' => 'required|integer|min:0',
            'reposicion' => 'required|integer|min:0',
            'costo' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:255',
        ]);

        $validated['proveedor'] = $this->normalizeProvider($validated['proveedor'] ?? null);

        InventoryItem::create($validated);

        return redirect()->route('inventario')->with('success', 'Material agregado correctamente.');
    }

    public function edit($id)
    {
        $item = InventoryItem::findOrFail($id);

        return view('inventario.editar', ['item' => $item->toArray()]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'material' => ['required', 'string', 'max:255', Rule::unique('inventory_items', 'material')->ignore($id)],
            'stock' => 'required|integer|min:0',
            'reposicion' => 'required|integer|min:0',
            'costo' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:255',
        ]);

        $validated['proveedor'] = $this->normalizeProvider($validated['proveedor'] ?? null);

        $item = InventoryItem::findOrFail($id);
        $item->update($validated);

        return redirect()->route('inventario')->with('success', 'Material actualizado correctamente.');
    }

    public function destroy($id)
    {
        $item = InventoryItem::find($id);

        if ($item) {
            $item->delete();
        }

        return redirect()->route('inventario')->with('success', 'Material eliminado correctamente.');
    }

    private function normalizeProvider(?string $provider): string
    {
        return trim((string) $provider) ?: 'Sin proveedor';
    }
}
