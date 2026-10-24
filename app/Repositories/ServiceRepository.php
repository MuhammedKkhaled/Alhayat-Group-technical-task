<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository
{
    /** @return Collection<int, Service> */
    public function findActiveForDoctor(int $doctorId, array $serviceIds): Collection
    {
        return Service::query()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->whereKey($serviceIds)
            ->get();
    }
}
