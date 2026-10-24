<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\AvailableSlotsRequest;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    public function index(AvailableSlotsRequest $request, int $doctor): JsonResponse
    {
        return response()->json([
            'slots' => $this->availability->getAvailableSlots(
                $doctor,
                $request->validated('service_ids'),
                $request->validated('date'),
            ),
        ]);
    }
}
