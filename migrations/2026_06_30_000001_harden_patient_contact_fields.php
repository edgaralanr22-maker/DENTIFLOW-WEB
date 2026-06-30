<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // El telefono es dato operativo obligatorio para citas, recordatorios y contacto clinico.
            $table->string('telefono', 20)->nullable(false)->change();

            // La validacion ya evita correos repetidos; el indice protege la integridad directamente en MySQL.
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('telefono')->nullable()->change();
        });
    }
};
