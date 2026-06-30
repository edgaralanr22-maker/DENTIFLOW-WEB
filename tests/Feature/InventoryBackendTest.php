<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_item_can_be_created_with_valid_data(): void
    {
        $response = $this->withSession(['access_role' => 'asistente'])->post(route('inventario.store'), [
            'material' => 'Algodon dental',
            'stock' => 25,
            'reposicion' => 10,
            'costo' => 120.50,
            'proveedor' => 'Proveedor Dental',
        ]);

        $response->assertRedirect(route('inventario'));
        $this->assertDatabaseHas('inventory_items', [
            'material' => 'Algodon dental',
            'stock' => 25,
            'reposicion' => 10,
        ]);
    }

    public function test_inventory_item_without_provider_is_saved_as_without_provider(): void
    {
        $response = $this->withSession(['access_role' => 'asistente'])->post(route('inventario.store'), [
            'material' => 'Cubrebocas sin proveedor',
            'stock' => 20,
            'reposicion' => 5,
            'costo' => 80,
            'proveedor' => '',
        ]);

        $response->assertRedirect(route('inventario'));
        $this->assertDatabaseHas('inventory_items', [
            'material' => 'Cubrebocas sin proveedor',
            'proveedor' => 'Sin proveedor',
        ]);

        $this->withSession(['access_role' => 'asistente'])
            ->get(route('inventario'))
            ->assertOk()
            ->assertSee('Cubrebocas sin proveedor')
            ->assertSee('Sin proveedor');
    }

    public function test_inventory_material_must_be_unique(): void
    {
        InventoryItem::create([
            'material' => 'Anestesia',
            'stock' => 10,
            'reposicion' => 5,
            'costo' => 200,
            'proveedor' => 'Proveedor Dental',
        ]);

        $response = $this->withSession(['access_role' => 'asistente'])->post(route('inventario.store'), [
            'material' => 'Anestesia',
            'stock' => 20,
            'reposicion' => 5,
            'costo' => 220,
            'proveedor' => 'Otro proveedor',
        ]);

        $response->assertSessionHasErrors('material');
    }

    public function test_inventory_item_can_be_updated_without_failing_own_name(): void
    {
        $item = InventoryItem::create([
            'material' => 'Resina',
            'stock' => 8,
            'reposicion' => 4,
            'costo' => 500,
            'proveedor' => 'Proveedor Dental',
        ]);

        $response = $this->withSession(['access_role' => 'asistente'])->post(route('inventario.update', $item->id), [
            'material' => 'Resina',
            'stock' => 12,
            'reposicion' => 4,
            'costo' => 510,
            'proveedor' => 'Proveedor Dental',
        ]);

        $response->assertRedirect(route('inventario'));
        $this->assertDatabaseHas('inventory_items', ['id' => $item->id, 'stock' => 12]);
    }

    public function test_inventory_item_provider_can_be_cleared_to_without_provider(): void
    {
        $item = InventoryItem::create([
            'material' => 'Batas',
            'stock' => 8,
            'reposicion' => 4,
            'costo' => 150,
            'proveedor' => 'Proveedor Dental',
        ]);

        $this->withSession(['access_role' => 'asistente'])->post(route('inventario.update', $item->id), [
            'material' => 'Batas',
            'stock' => 8,
            'reposicion' => 4,
            'costo' => 150,
            'proveedor' => '',
        ])->assertRedirect(route('inventario'));

        $this->assertDatabaseHas('inventory_items', [
            'id' => $item->id,
            'proveedor' => 'Sin proveedor',
        ]);
    }

    public function test_inventory_index_calculates_restock_alerts(): void
    {
        InventoryItem::create([
            'material' => 'Guantes',
            'stock' => 3,
            'reposicion' => 10,
            'costo' => 100,
            'proveedor' => 'Proveedor Dental',
        ]);

        $response = $this->withSession(['access_role' => 'asistente'])->get(route('inventario'));

        $response->assertOk();
        $response->assertSee('Productos en alerta');
        $response->assertSee('Guantes');
        $this->assertTrue(InventoryItem::first()->needsRestock());
    }

    public function test_inventory_provider_costs_leave_missing_provider_name_blank(): void
    {
        InventoryItem::create([
            'material' => 'Gasas',
            'stock' => 10,
            'reposicion' => 5,
            'costo' => 30,
            'proveedor' => 'Sin proveedor',
        ]);

        $this->withSession(['access_role' => 'asistente'])
            ->get(route('inventario'))
            ->assertOk()
            ->assertSee('<p class="font-semibold text-gray-800"></p>', false);
    }
}
