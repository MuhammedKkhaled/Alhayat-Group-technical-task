<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingRepositoryInterface $bookings,
        private readonly DoctorRepositoryInterface $doctors,
    ) {
    }

    /** @return array{booking: Booking, replayed: bool} */
    public function create(User $user, array $payload): array
    {
        $payloadHash = $this->payloadHash($payload);
        $existing = $this->bookings->findByIdempotencyKey($user->id, $payload['idempotency_key']);

        if ($existing) {
            return $this->replay($existing, $payloadHash);
        }

        try {
            return DB::transaction(function () use ($user, $payload, $payloadHash) {
                $doctor = $this->doctors->lockActive($payload['doctor_id']);
                $existing = $this->bookings->findByIdempotencyKey($user->id, $payload['idempotency_key']);

                if ($existing) {
                    return $this->replay($existing, $payloadHash);
                }

                $appointment = $this->availability->prepare(
                    $doctor,
                    $payload['service_ids'],
                    $payload['date'],
                    $payload['start_time'],
                );

                return [
                    'booking' => $this->bookings->create(
                        $user,
                        $doctor,
                        $appointment['services'],
                        $appointment['starts_at'],
                        $appointment['ends_at'],
                        $appointment['total_price'],
                        $appointment['total_duration_minutes'],
                        $payload['idempotency_key'],
                        $payloadHash,
                    ),
                    'replayed' => false,
                ];
            }, attempts: 3);
        } catch (QueryException $exception) {
            $existing = $this->bookings->findByIdempotencyKey($user->id, $payload['idempotency_key']);

            if ($existing) {
                return $this->replay($existing, $payloadHash);
            }

            throw $exception;
        }
    }

    private function replay(Booking $booking, string $payloadHash): array
    {
        if (! hash_equals((string) $booking->idempotency_payload_hash, $payloadHash)) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'This Idempotency-Key has already been used with different booking details.',
            ]);
        }

        return ['booking' => $booking, 'replayed' => true];
    }

    private function payloadHash(array $payload): string
    {
        $serviceIds = array_map('intval', $payload['service_ids']);
        sort($serviceIds, SORT_NUMERIC);

        return hash('sha256', json_encode([
            'doctor_id' => (int) $payload['doctor_id'],
            'service_ids' => $serviceIds,
            'date' => $payload['date'],
            'start_time' => $payload['start_time'],
        ], JSON_THROW_ON_ERROR));
    }
}
