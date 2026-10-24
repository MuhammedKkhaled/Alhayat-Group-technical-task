<?php

namespace App\Repositories\Contracts;

use App\Models\Doctor;
use Illuminate\Support\Collection;

interface DoctorRepositoryInterface
{
    public function activeDoctors(): Collection;

    public function findActive(int $doctorId): Doctor;

    public function findActiveWithServices(int $doctorId): Doctor;

    public function lockActive(int $doctorId): Doctor;
}
