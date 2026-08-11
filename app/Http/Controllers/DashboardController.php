<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all-time'); // daily, weekly, monthly, yearly, all-time

        $query = Booking::query();
        $startDate = null;

        if ($filter !== 'all-time') {
            $startDate = match ($filter) {
                'daily' => Carbon::today(),
                'weekly' => Carbon::now()->startOfWeek(),
                'monthly' => Carbon::now()->startOfMonth(),
                'yearly' => Carbon::now()->startOfYear(),
                default => null,
            };
            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }
        }

        $totalBookings = (clone $query)->count();
        $totalRevenue = (clone $query)->whereIn('status', ['confirmed', 'completed'])->sum('total_price');
        $confirmedEvents = (clone $query)->where('status', 'confirmed')->count();

        // Popular Packages (Top 4) based on the filter timeframe
        $popularPackages = Package::withCount(['bookings' => function ($q) use ($startDate) {
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
        }])
            ->orderByDesc('bookings_count')
            ->take(4)
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'bookings_count' => $package->bookings_count,
                    'new_today' => Booking::where('package_id', $package->id)
                        ->whereDate('created_at', Carbon::today())
                        ->count(),
                ];
            });

        // Upcoming Bookings: next 5 bookings by event_date (independent of created_at filter,
        // usually upcoming means future events, regardless of when they were booked,
        // though we could filter them but usually upcoming is just future).
        $upcomingBookings = Booking::with('package')
            ->where('event_date', '>=', Carbon::today())
            ->orderBy('event_date', 'asc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'filters' => ['filter' => $filter],
            'metrics' => [
                'totalBookings' => $totalBookings,
                'totalRevenue' => number_format($totalRevenue, 0),
                'confirmedEvents' => $confirmedEvents,
            ],
            'popularPackages' => $popularPackages,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }
}
