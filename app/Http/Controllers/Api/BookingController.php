<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\BookingPreviewRequest;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Models\Booking;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingService $bookings,
    ) {
    }

    public function preview(BookingPreviewRequest $request): JsonResponse
    {
        try {
            return response()->json($this->previewResponse($this->availability->preview(
                $request->validated('doctor_id'),
                $request->validated('service_ids'),
                $request->validated('date'),
                $request->validated('start_time'),
            )));
        } catch (SlotUnavailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $result = $this->bookings->create($request->user(), $request->validated());

            return response()->json(
                $this->bookingResponse($result['booking']),
                $result['replayed'] ? 200 : 201,
            );
        } catch (SlotUnavailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    private function previewResponse(array $preview): array
    {
        return [
            'doctor' => ['id' => $preview['doctor']->id, 'name' => $preview['doctor']->name],
            'services' => $preview['services']->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
            ])->values(),
            'total_price' => $preview['total_price'],
            'total_duration_minutes' => $preview['total_duration_minutes'],
            'starts_at' => $preview['starts_at']->toIso8601String(),
            'ends_at' => $preview['ends_at']->toIso8601String(),
        ];
    }

    private function bookingResponse(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'status' => $booking->status->value,
            'doctor' => ['id' => $booking->doctor_id, 'name' => $booking->doctor_name_snapshot],
            'services' => $booking->services->map(fn ($service) => [
                'id' => $service->id,
                'name' => $service->pivot->service_name_snapshot,
                'price' => $service->pivot->price_snapshot,
                'duration_minutes' => $service->pivot->duration_minutes_snapshot,
            ])->values(),
            'total_price' => $booking->total_price,
            'total_duration_minutes' => $booking->total_duration_minutes,
            'starts_at' => $booking->starts_at->toIso8601String(),
            'ends_at' => $booking->ends_at->toIso8601String(),
        ];
    }
}
