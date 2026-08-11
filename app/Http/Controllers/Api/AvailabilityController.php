<?php

namespace App\Http\Controllers\Api;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

use OpenApi\Attributes as OAT;

#[OAT\Tag(
    name: 'Availability',
    description: 'API Endpoints for Availability Calendar'
)]
class AvailabilityController extends Controller
{
    #[OAT\Get(
        path: '/api/availability',
        operationId: 'getAvailability',
        summary: 'Get availability calendar for a given month and year',
        description: "Returns a list of dates for the requested month marked as 'Booked' or 'Available'.",
        tags: ['Availability']
    )]
    #[OAT\Parameter(
        name: 'month',
        description: 'Month number (1-12). Defaults to current month.',
        in: 'query',
        required: false,
        schema: new OAT\Schema(type: 'integer', maximum: 12, minimum: 1)
    )]
    #[OAT\Parameter(
        name: 'year',
        description: 'Year (e.g. 2024). Defaults to current year.',
        in: 'query',
        required: false,
        schema: new OAT\Schema(type: 'integer')
    )]
    #[OAT\Response(
        response: 200,
        description: 'Successful operation',
        content: new OAT\JsonContent(
            type: 'array',
            items: new OAT\Items(
                properties: [
                    new OAT\Property(property: 'date', type: 'string', format: 'date', example: '2024-08-01'),
                    new OAT\Property(property: 'status', type: 'string', example: 'Available'),
                ],
                type: 'object'
            )
        )
    )]
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000',
        ]);

        $month = $validated['month'] ?? Carbon::now()->month;
        $year = $validated['year'] ?? Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $blockedDates = BlockedDate::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        // Assume bookings that are not cancelled block the date
        // As per the test, 'Confirmed' is a status.
        $bookedDates = Booking::whereBetween('event_date', [$startDate, $endDate])
            ->where('status', '!=', BookingStatus::Cancelled)
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

        return response()->json($availability);
    }
}
