<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request, \App\Services\AvailabilityCalculator $calculator)
    {
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);
        $view = $request->query('view', 'month');

        if ($view === 'year') {
            $yearAvailability = [];
            for ($m = 1; $m <= 12; $m++) {
                $yearAvailability[$m] = $calculator->getMonthlyAvailability($m, $year);
            }

            return Inertia::render('Calendar/Index', [
                'view' => 'year',
                'yearAvailability' => $yearAvailability,
                'currentMonth' => $month,
                'currentYear' => $year,
            ]);
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Shared truth with the mobile app: day states come from the same
        // calculator (sweep-first, cancelled excluded). Bookings ride along
        // for the day-cell chips; blocked dates stay explicit for labels.
        $availability = collect($calculator->getMonthlyAvailability($month, $year))
            ->mapWithKeys(fn ($day) => [$day['date'] => $day['status']]);

        $bookings = Booking::with('package')
            ->whereBetween('event_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '!=', \App\Enums\BookingStatus::Cancelled->value)
            ->paginate(100)
            ->withQueryString();

        $blockedDates = BlockedDate::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString());

        return Inertia::render('Calendar/Index', [
            'view' => 'month',
            'bookings' => $bookings,
            'blockedDates' => $blockedDates,
            'availability' => $availability,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }

    public function toggleBlock(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $dateStr = Carbon::parse($validated['date'])->toDateString();

        if (Booking::whereDate('event_date', $dateStr)
            ->where('status', '!=', \App\Enums\BookingStatus::Cancelled->value)
            ->exists()) {
            return redirect()->back()->withErrors(['date' => 'Cannot block a date that already has bookings.']);
        }

        $blocked = BlockedDate::whereDate('date', $dateStr)->first();

        if ($blocked) {
            $blocked->delete();

            return redirect()->back()->with('success', 'Date unblocked successfully.');
        } else {
            BlockedDate::create(['date' => $dateStr]);

            return redirect()->back()->with('success', 'Date blocked successfully.');
        }
    }
}
