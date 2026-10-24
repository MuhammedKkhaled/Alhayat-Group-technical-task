<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_session_login_logout_and_guest_protection_work(): void
    {
        $user = User::query()->create([
            'name' => 'Patient',
            'email' => 'patient@example.test',
            'password' => Hash::make('password123'),
        ]);

        $this->get('/my-bookings')->assertRedirect('/login');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect('/my-bookings');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_registration_route_is_not_available_and_login_is_rate_limited(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get('/missing-page')->assertNotFound()->assertSee('Page not found');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'unknown@example.test',
                'password' => 'invalid-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/login', [
            'email' => 'unknown@example.test',
            'password' => 'invalid-password',
        ])->assertTooManyRequests();
    }
}
