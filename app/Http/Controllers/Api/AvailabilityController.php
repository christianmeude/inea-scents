<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Availability",
 *     description="API Endpoints for Availability Calendar"
 * )
 */
class AvailabilityController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/availability",
     *      operationId="getAvailability",
     *      tags={"Availability"},
     *      summary="Get availability calendar for a given month and year",
     *      description="Returns a list of dates for the requested month marked as 'Booked' or 'Available'.",
     *      @OA\Parameter(
     *          name="month",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", minimum=1, maximum=12),
     *          description="Month number (1-12). Defaults to current month."
     *      ),
     *      @OA\Parameter(
     *          name="year",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer"),
     *          description="Year (e.g. 2024). Defaults to current year."
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="date", type="string", format="date", example="2024-08-01"),
     *                  @OA\Property(property="status", type="string", example="Available")
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $blockedDates = BlockedDate::whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();

        // Assume bookings that are not cancelled block the date
        // As per the test, 'Confirmed' is a status.
        $bookedDates = Booking::whereBetween('event_date', [$startDate, $endDate])
            ->where('status', '!=', 'Cancelled')
            ->pluck('event_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();

        $allBlocked = array_unique(array_merge($blockedDates, $bookedDates));

        $availability = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $availability[] = [
                'date' => $dateString,
                'status' => in_array($dateString, $allBlocked) ? 'Booked' : 'Available',
            ];
        }

        return response()->json($availability);
    }
}
