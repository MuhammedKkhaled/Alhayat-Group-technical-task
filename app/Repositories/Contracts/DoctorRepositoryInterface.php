<?php

namespace App\Repositories\Contracts;

use App\Models\Doctor;
use Illuminate\Support\Collection;

interface DoctorRepositoryInterface
{
    public function activeDoctors(): Collection;

    public function findActive(int $doctorId): Doctor;

    public function findActiveBySlug(string $slug): Doctor;

    public function findActiveWithServices(int $doctorId): Doctor;

    public function findActiveWithServicesBySlug(string $slug): Doctor;

    public function lockActive(int $doctorId): Doctor;
}
