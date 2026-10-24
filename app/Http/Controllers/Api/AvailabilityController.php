<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\AvailableSlotsRequest;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly DoctorRepositoryInterface $doctors,
    ) {
    }

    public function index(AvailableSlotsRequest $request, string $doctor): JsonResponse
    {
        $doctor = $this->doctors->findActiveBySlug($doctor);

        return response()->json([
            'slots' => $this->availability->getAvailableSlots(
                $doctor->id,
                $request->validated('service_ids'),
                $request->validated('date'),
            ),
        ]);
    }
}
