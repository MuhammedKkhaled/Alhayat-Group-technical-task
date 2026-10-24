<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = Doctor::where('code', 'DR-001')->firstOrFail();

        foreach ([
            ['code' => 'CONSULT', 'name' => 'Consultation', 'price' => 300, 'duration_minutes' => 30],
            ['code' => 'FOLLOWUP', 'name' => 'Follow-up', 'price' => 180, 'duration_minutes' => 20],
        ] as $service) {
            $doctor->services()->firstOrCreate(
                ['code' => $service['code']],
                [...$service, 'is_active' => true],
            );
        }
    }
}
