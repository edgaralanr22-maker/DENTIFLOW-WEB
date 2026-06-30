<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropUnique('treatments_tratamiento_unique');
            $table->unique('appointment_id', 'treatments_appointment_unique');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropUnique('treatments_appointment_unique');
            $table->unique('tratamiento', 'treatments_tratamiento_unique');
        });
    }
};
