<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // El rol debe vivir en base de datos para que el acceso no dependa solo del formulario de login.
            $table->string('role', 20)->default('doctor')->after('email');
        });

        DB::table('users')->where('email', 'admin@gmail.com')->update(['role' => 'admin']);
        DB::table('users')->where('email', 'asistente@dentiflow.com')->update(['role' => 'asistente']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
