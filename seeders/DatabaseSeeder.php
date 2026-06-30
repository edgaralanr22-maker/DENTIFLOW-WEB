<?php

namespace Database\Seeders;

use App\Models\Dentist;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Administrador DentiFlow',
                'role' => 'admin',
                'password' => Hash::make('Admin123!'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'asistente@dentiflow.com'],
            [
                'name' => 'Asistente DentiFlow',
                'role' => 'asistente',
                'password' => Hash::make('Asistente123!'),
                'telefono' => '2220000000',
                'puesto' => 'Recepcion',
            ]
        );

        $doctor = User::updateOrCreate(
            ['email' => 'doctor@dentiflow.com'],
            [
                'name' => 'Dra. DentiFlow',
                'role' => 'doctor',
                'password' => Hash::make('Doctor123!'),
                'telefono' => '2220000001',
                'puesto' => 'Odontologia general',
            ]
        );

        Dentist::updateOrCreate(
            ['user_id' => $doctor->id],
            [
                'nombre' => $doctor->name,
                'telefono' => $doctor->telefono,
                'especialidad' => $doctor->puesto,
            ]
        );
    }
}
