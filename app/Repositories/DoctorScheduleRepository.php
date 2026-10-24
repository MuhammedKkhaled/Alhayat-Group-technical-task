<?php

namespace App\Repositories;

use App\Models\DoctorSchedule;
use Carbon\CarbonInterface;

class DoctorScheduleRepository
{
    public function isClosedOn(int $doctorId, CarbonInterface $date): bool
    {
        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $date->toDateString())
            ->where('is_available', false)
            ->exists();
    }

    public function availableWeeklySchedule(int $doctorId, CarbonInterface $date): ?DoctorSchedule
    {
        return DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_available', true)
            ->first();
    }
}
