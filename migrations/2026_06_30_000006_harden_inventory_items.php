<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('inventory_items')->whereNull('proveedor')->update(['proveedor' => 'Sin proveedor']);

        Schema::table('inventory_items', function (Blueprint $table) {
            // El material es la llave operativa del inventario actual; hacerlo unico evita duplicar stock.
            $table->unique('material', 'inventory_items_material_unique');

            // Indices para alertas de reposicion y reportes por proveedor.
            $table->index(['stock', 'reposicion'], 'inventory_items_stock_reposicion_index');
            $table->index('proveedor', 'inventory_items_proveedor_index');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropUnique('inventory_items_material_unique');
            $table->dropIndex('inventory_items_stock_reposicion_index');
            $table->dropIndex('inventory_items_proveedor_index');
        });
    }
};
