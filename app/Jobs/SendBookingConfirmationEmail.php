<?php

namespace App\Jobs;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendBookingConfirmationEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(public readonly int $bookingId)
    {
    }

    public function handle(): void
    {
        $booking = Booking::query()
            ->with(['user', 'doctor', 'services'])
            ->findOrFail($this->bookingId);

        Mail::to($booking->user)->send(new BookingConfirmedMail($booking));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Booking confirmation email failed.', [
            'booking_id' => $this->bookingId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
