<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'puesto')) {
                $table->string('puesto')->nullable()->after('telefono');
            }
        });

        DB::table('users')->updateOrInsert(
            ['email' => 'asistente@dentiflow.com'],
            [
                'name' => 'Asistente DentiFlow',
                'password' => Hash::make('Asistente123!'),
                'telefono' => 'No registrado',
                'puesto' => 'Recepcion y gestion de citas',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'asistente@dentiflow.com')->delete();

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'puesto')) {
                $table->dropColumn('puesto');
            }

            if (Schema::hasColumn('users', 'telefono')) {
                $table->dropColumn('telefono');
            }
        });
    }
};
