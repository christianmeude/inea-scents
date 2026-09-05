<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, \App\Services\DashboardMetrics $metricsService)
    {
        $filter = $request->query('filter', 'all-time');
        
        $metrics = $metricsService->getMetrics($filter);

        return Inertia::render('Dashboard', [
            'filters' => ['filter' => $filter],
            'metrics' => [
                'totalBookings' => $metrics['totalBookings'],
                'totalRevenue' => number_format($metrics['totalRevenue'], 0, '.', ''),
                'confirmedEvents' => $metrics['confirmedEvents'],
            ],
            'popularPackages' => $metrics['popularPackages'],
            'upcomingBookings' => $metrics['upcomingBookings'],
        ]);
    }
}
