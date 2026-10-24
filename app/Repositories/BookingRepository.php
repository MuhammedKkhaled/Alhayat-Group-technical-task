<?php

namespace App\Repositories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    public function hasConfirmedOverlap(int $doctorId, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        return Booking::query()
            ->where('doctor_id', $doctorId)
            ->where('status', BookingStatus::Confirmed->value)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    public function findByIdempotencyKey(int $userId, string $key): ?Booking
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->where('idempotency_key', $key)
            ->with(['doctor', 'services'])
            ->first();
    }

    public function bookingsForUser(int $userId): Collection
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->with(['doctor', 'services'])
            ->latest('starts_at')
            ->get();
    }

    public function findForUser(int $bookingId, int $userId): Booking
    {
        return Booking::query()
            ->whereKey($bookingId)
            ->where('user_id', $userId)
            ->with(['doctor', 'services'])
            ->firstOrFail();
    }

    /** @param Collection<int, Service> $services */
    public function create(
        User $user,
        Doctor $doctor,
        Collection $services,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        string $totalPrice,
        int $totalDuration,
        string $idempotencyKey,
        string $idempotencyPayloadHash,
    ): Booking {
        $booking = Booking::query()->create([
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'total_price' => $totalPrice,
            'total_duration_minutes' => $totalDuration,
            'status' => BookingStatus::Confirmed,
            'doctor_name_snapshot' => $doctor->name,
            'idempotency_key' => $idempotencyKey,
            'idempotency_payload_hash' => $idempotencyPayloadHash,
        ]);

        $booking->services()->attach($services->mapWithKeys(fn (Service $service) => [
            $service->id => [
                'service_name_snapshot' => $service->name,
                'price_snapshot' => $service->price,
                'duration_minutes_snapshot' => $service->duration_minutes,
            ],
        ])->all());

        return $booking->load(['doctor', 'services']);
    }
}
