<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained('patients')->cascadeOnDelete();
            $table->string('blood_type', 10)->nullable();
            $table->text('allergies')->nullable();
            $table->text('conditions')->nullable();
            $table->text('medications')->nullable();
            $table->text('surgeries')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('dentist_id')->nullable()->constrained('dentists')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->dateTime('recorded_at');
            $table->string('reason');
            $table->text('diagnosis')->nullable();
            $table->text('procedure')->nullable();
            $table->text('notes')->nullable();
            $table->text('prescription')->nullable();
            $table->date('next_visit')->nullable();
            $table->timestamps();
        });

        Schema::create('odontogram_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('dentist_id')->nullable()->constrained('dentists')->nullOnDelete();
            $table->unsignedTinyInteger('tooth_number');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['patient_id', 'tooth_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogram_entries');
        Schema::dropIfExists('clinical_records');
        Schema::dropIfExists('medical_histories');
    }
};
