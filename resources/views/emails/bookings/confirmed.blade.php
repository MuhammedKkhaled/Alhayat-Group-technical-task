<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Booking confirmation</title>
</head>
<body>
    <h1>Your booking is confirmed</h1>

    <p>Booking reference: #{{ $booking->id }}</p>
    <p>Doctor: {{ $booking->doctor_name_snapshot }}</p>
    <p>Date: {{ $booking->starts_at->format('F j, Y') }}</p>
    <p>Time: {{ $booking->starts_at->format('H:i') }}–{{ $booking->ends_at->format('H:i') }}</p>

    <h2>Services</h2>
    <ul>
        @foreach ($booking->services as $service)
            <li>
                {{ $service->pivot->service_name_snapshot }} —
                {{ $service->pivot->duration_minutes_snapshot }} minutes —
                {{ number_format((float) $service->pivot->price_snapshot, 2) }}
            </li>
        @endforeach
    </ul>

    <p>Total duration: {{ $booking->total_duration_minutes }} minutes</p>
    <p>Total price: {{ number_format((float) $booking->total_price, 2) }}</p>
</body>
</html>
