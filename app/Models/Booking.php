<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'doctor_id', 'starts_at', 'ends_at', 'total_price',
        'total_duration_minutes', 'status', 'doctor_name_snapshot', 'rescheduled_at',
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
}
