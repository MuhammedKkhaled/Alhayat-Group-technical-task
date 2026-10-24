<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\BookingPreviewRequest;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\Cache\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingService $bookings,
        private readonly CacheService $cache,
        private readonly BookingRepositoryInterface $bookingRepository,
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

            return (new BookingResource($result['booking']))
                ->response()
                ->setStatusCode($result['replayed'] ? 200 : 201);
        } catch (SlotUnavailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    public function index(Request $request)
    {
        return BookingResource::collection($this->cache->getUserBookings($request->user()->id));
    }

    public function show(Request $request, string $booking): BookingResource
    {
        return new BookingResource(
            $this->bookingRepository->findForUser($booking, $request->user()->id),
        );
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
}
