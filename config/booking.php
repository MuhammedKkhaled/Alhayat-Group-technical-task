<?php

return [
    'slot_interval' => max(1, (int) env('BOOKING_SLOT_INTERVAL', 30)),
    'user_bookings_ttl' => max(1, (int) env('BOOKING_USER_CACHE_TTL', 3600)),
];
