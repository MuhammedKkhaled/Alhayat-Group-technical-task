<?php

namespace App\Repositories\Contracts;

use App\Models\Doctor;

interface DoctorRepositoryInterface
{
    public function findActive(int $doctorId): Doctor;

    public function lockActive(int $doctorId): Doctor;
}
