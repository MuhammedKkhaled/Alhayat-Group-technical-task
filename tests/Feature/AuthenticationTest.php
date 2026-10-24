<?php

namespace Tests\Feature;

use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use Egulias\EmailValidator\EmailValidator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_session_login_logout_and_guest_protection_work(): void
    {
        $this->mock(EmailValidator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isValid')->andReturnTrue();
        });

        $user = User::query()->create([
            'name' => 'Patient',
            'email' => 'patient@example.com',
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
        $this->mock(EmailValidator::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isValid')->andReturnTrue();
        });

        $this->get('/register')->assertNotFound();
        $this->get('/missing-page')->assertNotFound()->assertSee('Page not found');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'unknown@example.com',
                'password' => 'invalid-password',
            ])->assertUnprocessable()
                ->assertJsonValidationErrors('email');
        }

        $this->postJson('/api/login', [
            'email' => 'unknown@example.com',
            'password' => 'invalid-password',
        ])->assertTooManyRequests();
    }

    public function test_login_requires_an_rfc_dns_email_for_an_existing_user(): void
    {
        $this->assertSame(
            ['bail', 'required', 'email:rfc,dns', 'exists:users,email'],
            (new LoginRequest())->rules()['email'],
        );
    }
}
