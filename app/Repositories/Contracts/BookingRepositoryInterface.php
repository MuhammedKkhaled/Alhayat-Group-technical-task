<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use App\Models\Doctor;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface BookingRepositoryInterface
{
    public function hasConfirmedOverlap(int $doctorId, CarbonInterface $startsAt, CarbonInterface $endsAt): bool;

    public function findByIdempotencyKey(int $userId, string $key): ?Booking;

    public function bookingsForUser(int $userId): Collection;

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
    ): Booking;
}
