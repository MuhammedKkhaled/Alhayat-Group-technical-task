<?php

namespace App\Models;

use App\Enums\WeekDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id', 'day_of_week', 'date', 'start_time', 'end_time', 'is_available',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => WeekDay::class,
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
