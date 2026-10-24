<?php

namespace App\Services\Cache;

use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function __construct(private readonly BookingRepositoryInterface $bookings)
    {
    }

    public function userBookingsKey(int $userId): string
    {
        return "user_bookings_{$userId}";
    }

    public function getUserBookings(int $userId): Collection
    {
        return Cache::store('redis')->remember(
            $this->userBookingsKey($userId),
            now()->addSeconds(config('booking.user_bookings_ttl')),
            fn () => $this->bookings->bookingsForUser($userId),
        );
    }

    public function cacheUserBookings(int $userId): Collection
    {
        $this->forgetUserBookings($userId);
        $bookings = $this->bookings->bookingsForUser($userId);

        Cache::store('redis')->put(
            $this->userBookingsKey($userId),
            $bookings,
            now()->addSeconds(config('booking.user_bookings_ttl')),
        );

        return $bookings;
    }

    public function forgetUserBookings(int $userId): void
    {
        Cache::store('redis')->forget($this->userBookingsKey($userId));
    }
}
