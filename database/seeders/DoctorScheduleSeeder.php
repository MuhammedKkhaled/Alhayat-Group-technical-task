<?php

namespace Database\Seeders;

use App\Enums\WeekDay;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Doctor::where('code', 'DR-001')->firstOrFail();

        foreach (WeekDay::cases() as $day) {
            $available = ! in_array($day, [WeekDay::Friday, WeekDay::Saturday], true);

            $doctor->schedules()->firstOrCreate(
                ['day_of_week' => $day->value],
                [
                    'is_available' => $available,
                    'start_time' => $available ? '09:00' : null,
                    'end_time' => $available ? '17:00' : null,
                ],
            );
        }
    }
}
