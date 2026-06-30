<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dentist_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_id')->constrained('dentists')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->boolean('enabled')->default(true);
            $table->time('start_time')->default('09:00');
            $table->time('end_time')->default('17:00');
            $table->timestamps();
            $table->unique(['dentist_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dentist_schedules');
    }
};
