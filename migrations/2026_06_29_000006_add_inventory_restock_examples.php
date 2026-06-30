<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $items = [
            ['material' => 'Guantes de nitrilo', 'stock' => 8, 'reposicion' => 20, 'costo' => 185.00, 'proveedor' => 'Dental Supply MX'],
            ['material' => 'Anestesia dental', 'stock' => 5, 'reposicion' => 10, 'costo' => 420.00, 'proveedor' => 'OdontoMed'],
            ['material' => 'Resina fotocurable', 'stock' => 3, 'reposicion' => 6, 'costo' => 690.00, 'proveedor' => 'Insumos Dentales del Centro'],
        ];

        foreach ($items as $item) {
            DB::table('inventory_items')->updateOrInsert(
                ['material' => $item['material']],
                $item + ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('inventory_items')->whereIn('material', [
            'Guantes de nitrilo',
            'Anestesia dental',
            'Resina fotocurable',
        ])->delete();
    }
};
