<?php

return [
    'slot_interval' => max(1, (int) env('BOOKING_SLOT_INTERVAL', 30)),
];
