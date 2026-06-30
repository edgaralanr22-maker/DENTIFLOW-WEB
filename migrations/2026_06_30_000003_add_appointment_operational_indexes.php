<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Indices para validar agenda, choques de horario y listados por doctor/fecha sin tocar el front.
            $table->index(['dentist_id', 'date', 'time'], 'appointments_dentist_date_time_index');
            $table->index(['patient_id', 'date'], 'appointments_patient_date_index');
            $table->index('estado', 'appointments_estado_index');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_dentist_date_time_index');
            $table->dropIndex('appointments_patient_date_index');
            $table->dropIndex('appointments_estado_index');
        });
    }
};
