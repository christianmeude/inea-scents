<?php

namespace App\Services;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;

class AvailabilityCalculator
{
    /**
     * Get the availability calendar for a specific month and year.
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getMonthlyAvailability(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $blockedDates = BlockedDate::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        // 1 Booking per day rule: Any active (not cancelled) booking blocks the date.
        $bookedDates = Booking::whereBetween('event_date', [$startDate, $endDate])
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->pluck('event_date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        $allBlocked = array_unique(array_merge($blockedDates, $bookedDates));

        $availability = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $availability[] = [
                'date' => $dateString,
                'status' => in_array($dateString, $allBlocked) ? AvailabilityStatus::Booked->value : AvailabilityStatus::Available->value,
            ];
        }

        return $availability;
    }
}
