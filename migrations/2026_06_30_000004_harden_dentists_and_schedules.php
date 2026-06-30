<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('dentists')
            ->whereNull('telefono')
            ->orWhere('telefono', '')
            ->update(['telefono' => 'No registrado']);

        Schema::table('dentists', function (Blueprint $table) {
            // El nombre se usa en formularios existentes; hacerlo unico evita seleccionar al doctor equivocado.
            $table->unique('nombre', 'dentists_nombre_unique');

            // El telefono queda obligatorio para mantener contacto operativo del profesional.
            $table->string('telefono', 50)->nullable(false)->change();
        });

        Schema::table('dentist_schedules', function (Blueprint $table) {
            // Consultas de disponibilidad por doctor/dia para agenda y citas.
            $table->index(['dentist_id', 'weekday', 'enabled'], 'dentist_schedules_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('dentist_schedules', function (Blueprint $table) {
            $table->dropIndex('dentist_schedules_lookup_index');
        });

        Schema::table('dentists', function (Blueprint $table) {
            $table->dropUnique('dentists_nombre_unique');
            $table->string('telefono')->nullable()->change();
        });
    }
};
