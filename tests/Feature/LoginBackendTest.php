<?php

namespace Tests\Feature;

use App\Models\Dentist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_login_with_matching_role(): void
    {
        $doctor = User::create([
            'name' => 'Doctor Login',
            'email' => 'doctor@dentiflow.com',
            'role' => 'doctor',
            'password' => 'password',
        ]);
        Dentist::create([
            'user_id' => $doctor->id,
            'nombre' => $doctor->name,
            'telefono' => 'No registrado',
        ]);

        $this->post('/login', [
            'role' => 'doctor',
            'email' => 'doctor@dentiflow.com',
            'password' => 'password',
        ])
            ->assertRedirect(route('inicio'))
            ->assertSessionHas('access_role', 'doctor')
            ->assertSessionHas('access_email', 'doctor@dentiflow.com');
    }
}
