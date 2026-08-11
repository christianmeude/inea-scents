<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'Available';
    case Booked = 'Booked';
}
