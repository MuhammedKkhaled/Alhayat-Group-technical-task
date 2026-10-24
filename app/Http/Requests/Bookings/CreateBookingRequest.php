<?php

namespace App\Http\Requests\Bookings;

class CreateBookingRequest extends BookingInputRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }
}
