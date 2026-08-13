<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $bookings = Booking::with('package')
            ->whereBetween('event_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $blockedDates = BlockedDate::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->pluck('date')
            ->map(fn ($date) => $date->toDateString());

        return Inertia::render('Calendar/Index', [
            'bookings' => $bookings,
            'blockedDates' => $blockedDates,
            'currentMonth' => (int) $month,
            'currentYear' => (int) $year,
        ]);
    }

    public function toggleBlock(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        $dateStr = Carbon::parse($validated['date'])->toDateString();

        if (Booking::whereDate('event_date', $dateStr)->exists()) {
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
