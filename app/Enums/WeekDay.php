<?php

namespace App\Enums;

/** PHP/Carbon numbering: Sunday = 0 through Saturday = 6. */
enum WeekDay: int
{
    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
}
