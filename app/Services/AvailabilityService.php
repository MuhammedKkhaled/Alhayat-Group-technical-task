<?php

namespace App\Services;

use App\Exceptions\SlotUnavailableException;
use App\Models\Doctor;
use App\Models\Service;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\DoctorScheduleRepository;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    public function __construct(
        private readonly DoctorRepositoryInterface $doctors,
        private readonly ServiceRepositoryInterface $services,
        private readonly DoctorScheduleRepository $schedules,
        private readonly BookingRepositoryInterface $bookings,
    ) {
    }

    public function getAvailableSlots(int $doctorId, array $serviceIds, string $date): array
    {
        $doctor = $this->doctors->findActive($doctorId);
        $selection = $this->selection($doctor, $serviceIds);
        $day = $this->date($date);

        if ($this->schedules->isClosedOn($doctor->id, $day)) {
            return [];
        }

        $schedule = $this->schedules->availableWeeklySchedule($doctor->id, $day);

        if (! $schedule || ! $schedule->start_time || ! $schedule->end_time) {
            return [];
        }

        $workdayStartsAt = $this->atTime($day, $schedule->start_time);
        $workdayEndsAt = $this->atTime($day, $schedule->end_time);
        $slots = [];

        for ($startsAt = $workdayStartsAt; $startsAt->addMinutes($selection['total_duration_minutes'])->lte($workdayEndsAt); $startsAt = $startsAt->addMinutes(config('booking.slot_interval'))) {
            $endsAt = $startsAt->addMinutes($selection['total_duration_minutes']);

            if ($startsAt->lte(now($startsAt->getTimezone())) || $this->bookings->hasConfirmedOverlap($doctor->id, $startsAt, $endsAt)) {
                continue;
            }

            $slots[] = [
                'start_time' => $startsAt->format('H:i'),
                'end_time' => $endsAt->format('H:i'),
            ];
        }

        return $slots;
    }

    public function preview(int $doctorId, array $serviceIds, string $date, string $startTime): array
    {
        return $this->prepare($this->doctors->findActive($doctorId), $serviceIds, $date, $startTime);
    }

    public function prepare(Doctor $doctor, array $serviceIds, string $date, string $startTime): array
    {
        $selection = $this->selection($doctor, $serviceIds);
        $day = $this->date($date);
        $startsAt = $this->atTime($day, $startTime);
        $endsAt = $startsAt->addMinutes($selection['total_duration_minutes']);

        if ($startsAt->lte(now($startsAt->getTimezone())) || $this->schedules->isClosedOn($doctor->id, $day)) {
            throw new SlotUnavailableException();
        }

        $schedule = $this->schedules->availableWeeklySchedule($doctor->id, $day);

        if (! $schedule || ! $schedule->start_time || ! $schedule->end_time) {
            throw new SlotUnavailableException();
        }

        $workdayStartsAt = $this->atTime($day, $schedule->start_time);
        $workdayEndsAt = $this->atTime($day, $schedule->end_time);
        $minutesFromWorkdayStart = $workdayStartsAt->diffInMinutes($startsAt, false);

        if ($minutesFromWorkdayStart < 0 || $minutesFromWorkdayStart % config('booking.slot_interval') !== 0 || $endsAt->gt($workdayEndsAt) || $this->bookings->hasConfirmedOverlap($doctor->id, $startsAt, $endsAt)) {
            throw new SlotUnavailableException();
        }

        return [
            ...$selection,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }

    private function selection(Doctor $doctor, array $serviceIds): array
    {
        $serviceIds = array_map('intval', $serviceIds);

        if ($serviceIds === [] || count($serviceIds) !== count(array_unique($serviceIds))) {
            throw ValidationException::withMessages(['service_ids' => 'Select each service only once.']);
        }

        $services = $this->services->findActiveForDoctor($doctor->id, $serviceIds);

        if ($services->count() !== count($serviceIds)) {
            throw ValidationException::withMessages(['service_ids' => 'Every selected service must be active and belong to this doctor.']);
        }

        $order = array_flip($serviceIds);
        $services = $services->sortBy(fn ($service) => $order[$service->id])->values();

        $totalCents = $services->sum(fn (Service $service) => $this->priceInCents($service));

        return [
            'doctor' => $doctor,
            'services' => $services,
            'total_price' => sprintf('%d.%02d', intdiv($totalCents, 100), $totalCents % 100),
            'total_duration_minutes' => $services->sum('duration_minutes'),
        ];
    }

    private function date(string $date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date, config('app.timezone'));
    }

    private function atTime(CarbonImmutable $date, string $time): CarbonImmutable
    {
        return $date->setTimeFromTimeString($time);
    }

    private function priceInCents(Service $service): int
    {
        [$whole, $fraction] = array_pad(explode('.', $service->getRawOriginal('price')), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
