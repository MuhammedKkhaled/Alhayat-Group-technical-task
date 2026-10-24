<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Repositories\Contracts\DoctorRepositoryInterface;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function findActive(int $doctorId): Doctor
    {
        return Doctor::query()
            ->whereKey($doctorId)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function lockActive(int $doctorId): Doctor
    {
        return Doctor::query()
            ->whereKey($doctorId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
