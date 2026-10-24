<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Services\Cache\CacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'doctor_id', 'starts_at', 'ends_at', 'total_price',
        'total_duration_minutes', 'status', 'idempotency_key', 'idempotency_payload_hash',
        'doctor_name_snapshot', 'rescheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'total_price' => 'decimal:2',
            'total_duration_minutes' => 'integer',
            'status' => BookingStatus::class,
            'rescheduled_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saved(fn (Booking $booking) => static::refreshUserBookingsAfterCommit($booking->user_id));
        static::deleted(fn (Booking $booking) => static::refreshUserBookingsAfterCommit($booking->user_id));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withTrashed()
            ->withPivot(['service_name_snapshot', 'price_snapshot', 'duration_minutes_snapshot'])
            ->withTimestamps();
    }

    private static function refreshUserBookingsAfterCommit(int $userId): void
    {
        DB::afterCommit(fn () => app(CacheService::class)->cacheUserBookings($userId));
    }
}
