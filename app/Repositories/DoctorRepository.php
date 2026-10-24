<?php

namespace App\Repositories;

use App\Models\Doctor;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Illuminate\Support\Collection;

class DoctorRepository implements DoctorRepositoryInterface
{
    public function activeDoctors(): Collection
    {
        return Doctor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findActive(int $doctorId): Doctor
    {
        return Doctor::query()
            ->whereKey($doctorId)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function findActiveWithServices(int $doctorId): Doctor
    {
        return Doctor::query()
            ->whereKey($doctorId)
            ->where('is_active', true)
            ->with(['services' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
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
