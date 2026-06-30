<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->string('clinic_name', 100)->default('Clinica DentiFlow');
            $table->string('support_email', 150)->default('soporte@dentiflow.com');
            $table->unsignedSmallInteger('appointment_duration')->default(60);
            $table->unsignedSmallInteger('schedule_interval')->default(30);
            $table->time('opening_time')->default('09:00:00');
            $table->time('closing_time')->default('18:00:00');
            $table->string('default_appointment_status', 30)->default('Pendiente');
            $table->boolean('automatic_reminders_enabled')->default(true);
            $table->boolean('inventory_alerts_enabled')->default(true);
            $table->boolean('maintenance_mode_enabled')->default(false);
            $table->boolean('administrative_audit_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};
