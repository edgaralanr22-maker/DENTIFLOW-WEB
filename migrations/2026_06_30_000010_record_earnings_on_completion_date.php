<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('treatments')
            ->whereNotNull('appointment_id')
            ->where('estado', 'Realizado')
            ->orderBy('id')
            ->each(function ($treatment) {
                DB::table('treatments')
                    ->where('id', $treatment->id)
                    ->update(['fecha' => substr((string) $treatment->created_at, 0, 10)]);
            });
    }

    public function down(): void
    {
        DB::table('treatments')
            ->whereNotNull('appointment_id')
            ->where('estado', 'Realizado')
            ->orderBy('id')
            ->each(function ($treatment) {
                $appointmentDate = DB::table('appointments')
                    ->where('id', $treatment->appointment_id)
                    ->value('date');

                if ($appointmentDate) {
                    DB::table('treatments')
                        ->where('id', $treatment->id)
                        ->update(['fecha' => $appointmentDate]);
                }
            });
    }
};
