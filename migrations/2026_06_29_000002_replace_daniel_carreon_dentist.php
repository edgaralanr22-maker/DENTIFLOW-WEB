<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $daniel = DB::table('dentists')
            ->whereRaw('LOWER(nombre) = ?', ['daniel carreon'])
            ->first();

        if (!$daniel) {
            return;
        }

        $doctorName = DB::table('users')
            ->where('email', 'laura.ramirez@dentiflow.com')
            ->value('name') ?: 'Dr. Samuel';

        $doctorId = DB::table('dentists')
            ->where('nombre', $doctorName)
            ->value('id');

        if (!$doctorId) {
            $doctorId = DB::table('dentists')->insertGetId([
                'nombre' => $doctorName,
                'especialidad' => 'Odontología general',
                'telefono' => 'No registrado',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('appointments')
            ->where('dentist_id', $daniel->id)
            ->update(['dentist_id' => $doctorId, 'updated_at' => now()]);

        DB::table('dentists')->where('id', $daniel->id)->delete();
    }

    public function down(): void
    {
        // La reasignación de citas se conserva para evitar pérdida de información.
    }
};
