<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\ServiceResource;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    public function __construct(private readonly DoctorRepositoryInterface $doctors)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return DoctorResource::collection($this->doctors->activeDoctors());
    }

    public function services(string $doctor): AnonymousResourceCollection
    {
        return ServiceResource::collection(
            $this->doctors->findActiveWithServicesBySlug($doctor)->services,
        );
    }
}
