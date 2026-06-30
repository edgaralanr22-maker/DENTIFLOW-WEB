<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('treatments')->whereNull('paciente')->update(['paciente' => '']);
        DB::table('treatments')->whereNull('fecha')->update(['fecha' => now()->toDateString()]);
        DB::table('treatments')->whereNull('estado')->update(['estado' => 'Activo']);

        Schema::table('treatments', function (Blueprint $table) {
            // El catalogo se selecciona por nombre desde citas; un nombre unico evita ambiguedad.
            $table->unique('tratamiento', 'treatments_tratamiento_unique');

            // Indices usados por historial, reportes y filtros del catalogo.
            $table->index(['tipo', 'tratamiento'], 'treatments_tipo_tratamiento_index');
            $table->index(['estado', 'fecha'], 'treatments_estado_fecha_index');
            $table->index(['patient_id', 'fecha'], 'treatments_patient_fecha_index');
            $table->index('appointment_id', 'treatments_appointment_index');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropUnique('treatments_tratamiento_unique');
            $table->dropIndex('treatments_tipo_tratamiento_index');
            $table->dropIndex('treatments_estado_fecha_index');
            $table->dropIndex('treatments_patient_fecha_index');
            $table->dropIndex('treatments_appointment_index');
        });
    }
};
