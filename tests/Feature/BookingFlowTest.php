<?php

namespace Tests\Feature;

use App\Enums\WeekDay;
use App\Jobs\SendBookingConfirmationEmail;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.stores.redis' => ['driver' => 'array', 'serialize' => false]]);
    }

    public function test_web_booking_flow_renders_inertia_pages_and_queues_confirmation_after_creation(): void
    {
        Queue::fake();
        [$doctor, $service, $date] = $this->bookingData();
        $user = $this->user('patient@example.test');

        $this->inertiaGet('/doctors')
            ->assertOk()
            ->assertJsonPath('component', 'Doctors/Index')
            ->assertJsonPath('props.doctors.0.id', $doctor->id);

        $this->inertiaGet("/doctors/{$doctor->slug}")
            ->assertOk()
            ->assertJsonPath('component', 'Doctors/Show');

        $this->actingAs($user)
            ->inertiaGet("/bookings/create?doctor_id={$doctor->id}&service_ids[]={$service->id}")
            ->assertOk()
            ->assertJsonPath('component', 'Bookings/Create');

        $payload = $this->payload($doctor, $service, $date, '09:00');

        $this->actingAs($user)
            ->getJson("/doctors/{$doctor->slug}/availability?".http_build_query([
                'date' => $date,
                'service_ids' => [$service->id],
            ]))
            ->assertOk()
            ->assertJsonPath('slots.0.start_time', '09:00');

        $this->actingAs($user)
            ->postJson('/bookings/preview', $payload)
            ->assertOk()
            ->assertJsonPath('total_duration_minutes', 60)
            ->assertJsonPath('total_price', '150.00');

        $this->actingAs($user)
            ->withHeader('Idempotency-Key', 'web-booking-key')
            ->post('/bookings', $payload)
            ->assertRedirect('/my-bookings')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('bookings', 1);
        Queue::assertPushed(SendBookingConfirmationEmail::class, function (SendBookingConfirmationEmail $job): bool {
            return $job->connection === 'database' && $job->queue === 'email';
        });

        $this->actingAs($user)
            ->inertiaGet('/my-bookings')
            ->assertOk()
            ->assertJsonPath('component', 'Bookings/Index')
            ->assertJsonPath('props.bookings.0.slug', fn ($slug) => is_string($slug))
            ->assertJsonPath('props.bookings.0.services.0.name', 'Consultation');
    }

    public function test_api_booking_rejects_overlaps_and_keeps_cached_lists_isolated(): void
    {
        Queue::fake();
        [$doctor, $service, $date] = $this->bookingData();
        $firstUser = $this->user('first@example.test');
        $secondUser = $this->user('second@example.test');

        $this->asApiUser($firstUser)
            ->withHeader('Idempotency-Key', 'first-booking')
            ->postJson('/api/bookings', $this->payload($doctor, $service, $date, '09:00'))
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed');

        $this->asApiUser($secondUser)
            ->withHeader('Idempotency-Key', 'overlapping-booking')
            ->postJson('/api/bookings', $this->payload($doctor, $service, $date, '09:30'))
            ->assertConflict();

        $this->asApiUser($secondUser)
            ->withHeader('Idempotency-Key', 'second-booking')
            ->postJson('/api/bookings', $this->payload($doctor, $service, $date, '10:00'))
            ->assertCreated();

        $this->asApiUser($firstUser)
            ->getJson('/api/my-bookings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', fn ($slug) => is_string($slug));

        $this->asApiUser($secondUser)
            ->getJson('/api/my-bookings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', fn ($slug) => is_string($slug));
    }

    public function test_booking_rejects_other_doctor_services_and_replays_an_idempotent_request(): void
    {
        Queue::fake();
        [$doctor, $service, $date] = $this->bookingData();
        [, $otherService] = $this->bookingData('DR-002');
        $user = $this->user('patient@example.test');
        $payload = $this->payload($doctor, $service, $date, '09:00');

        $this->asApiUser($user)
            ->withHeader('Idempotency-Key', 'wrong-service')
            ->postJson('/api/bookings', [...$payload, 'service_ids' => [$otherService->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_ids');

        $first = $this->asApiUser($user)
            ->withHeader('Idempotency-Key', 'replay-key')
            ->postJson('/api/bookings', $payload)
            ->assertCreated();

        $this->asApiUser($user)
            ->withHeader('Idempotency-Key', 'replay-key')
            ->postJson('/api/bookings', $payload)
            ->assertOk()
            ->assertJsonPath('data.slug', $first->json('data.slug'));

        $this->assertDatabaseCount('bookings', 1);
        Queue::assertPushed(SendBookingConfirmationEmail::class, 1);
    }

    public function test_booking_details_are_scoped_to_the_authenticated_user(): void
    {
        Queue::fake();
        [$doctor, $service, $date] = $this->bookingData();
        $owner = $this->user('owner@example.test');
        $otherUser = $this->user('other@example.test');

        $response = $this->asApiUser($owner)
            ->withHeader('Idempotency-Key', 'scoped-booking')
            ->postJson('/api/bookings', $this->payload($doctor, $service, $date, '09:00'))
            ->assertCreated();

        $slug = $response->json('data.slug');

        $this->asApiUser($otherUser)
            ->getJson("/api/bookings/{$slug}")
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get("/bookings/{$slug}")
            ->assertNotFound();

        $this->inertiaGet('/missing-page')
            ->assertNotFound()
            ->assertJsonPath('component', 'Errors/NotFound');
    }

    private function bookingData(string $code = 'DR-001'): array
    {
        $doctor = Doctor::query()->create([
            'code' => $code,
            'name' => "Dr. {$code}",
            'is_active' => true,
        ]);
        $service = Service::query()->create([
            'doctor_id' => $doctor->id,
            'code' => 'CONSULT',
            'name' => 'Consultation',
            'price' => 150,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        foreach (WeekDay::cases() as $day) {
            DoctorSchedule::query()->create([
                'doctor_id' => $doctor->id,
                'day_of_week' => $day,
                'is_available' => true,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ]);
        }

        return [$doctor, $service, CarbonImmutable::now()->addDays(7)->toDateString()];
    }

    private function user(string $email): User
    {
        return User::query()->create([
            'name' => $email,
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);
    }

    private function payload(Doctor $doctor, Service $service, string $date, string $startTime): array
    {
        return [
            'doctor_id' => $doctor->id,
            'service_ids' => [$service->id],
            'date' => $date,
            'start_time' => $startTime,
        ];
    }

    private function asApiUser(User $user): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($user->createToken('test')->plainTextToken);
    }

    private function inertiaGet(string $uri): \Illuminate\Testing\TestResponse
    {
        $version = app(\App\Http\Middleware\HandleInertiaRequests::class)->version(app('request'));

        return $this->get($uri, [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
        ]);
    }
}
