<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\AvailableSlotsRequest;
use App\Http\Requests\Bookings\BookingCreatePageRequest;
use App\Http\Requests\Bookings\BookingPreviewRequest;
use App\Http\Requests\Bookings\CreateBookingRequest;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\Cache\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingService $bookings,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly CacheService $cache,
        private readonly DoctorRepositoryInterface $doctors,
    ) {
    }

    public function create(BookingCreatePageRequest $request): Response
    {
        $doctor = $this->doctors->findActiveWithServices($request->validated('doctor_id'));
        $selectedIds = $request->validated('service_ids');
        $selectedServices = $doctor->services->whereIn('id', $selectedIds)->values();

        if ($selectedServices->count() !== count($selectedIds)) {
            throw ValidationException::withMessages([
                'service_ids' => 'Every selected service must be active and belong to this doctor.',
            ]);
        }

        return Inertia::render('Bookings/Create', [
            'doctor' => $doctor,
            'selectedServiceIds' => $selectedIds,
        ]);
    }

    public function availability(AvailableSlotsRequest $request, int $doctor): JsonResponse
    {
        return response()->json([
            'slots' => $this->availability->getAvailableSlots(
                $doctor,
                $request->validated('service_ids'),
                $request->validated('date'),
            ),
        ]);
    }

    public function preview(BookingPreviewRequest $request): JsonResponse
    {
        try {
            $preview = $this->availability->preview(
                $request->validated('doctor_id'),
                $request->validated('service_ids'),
                $request->validated('date'),
                $request->validated('start_time'),
            );

            return response()->json($this->previewResponse($preview));
        } catch (SlotUnavailableException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    public function store(CreateBookingRequest $request): RedirectResponse
    {
        try {
            $this->bookings->create($request->user(), $request->validated());
        } catch (SlotUnavailableException $exception) {
            throw ValidationException::withMessages(['start_time' => $exception->getMessage()]);
        }

        return to_route('bookings.index')->with('success', 'Your booking has been confirmed.');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Bookings/Index', [
            'bookings' => $this->cache->getUserBookings($request->user()->id),
        ]);
    }

    public function show(Request $request, int $booking): Response
    {
        return Inertia::render('Bookings/Show', [
            'booking' => $this->bookingRepository->findForUser($booking, $request->user()->id),
        ]);
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
