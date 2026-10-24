<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        Doctor::firstOrCreate(
            ['code' => 'DR-001'],
            ['name' => 'Dr. Amira Hassan', 'is_active' => true],
        );
    }
}
