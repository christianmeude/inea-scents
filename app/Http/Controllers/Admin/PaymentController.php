<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\WebhookAlertSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request, WebhookAlertSummary $alerts)
    {
        $bookings = Booking::with('package')->latest();

        if ($request->filled('method')) {
            $bookings->where('payment_method', $request->method);
        }

        if ($request->filled('status')) {
            $bookings->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $bookings->where(function ($query) use ($search) {
                $query->where('booking_reference', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $events = DB::table('webhook_events')->orderByDesc('created_at')->paginate(10, ['*'], 'events');

        $events->getCollection()->transform(function ($event) {
            $reference = null;
            $payload = json_decode((string) $event->payload, true);
            if (is_array($payload)) {
                $remarks = $payload['data']['attributes']['data']['attributes']['remarks'] ?? null;
                if (is_string($remarks) && $remarks !== '') {
                    $reference = $remarks;
                }
            }
            $event->booking_reference = $reference;
            $event->booking_id = $reference
                ? Booking::where('booking_reference', $reference)->value('id')
                : null;

            return $event;
        });

        $revenue = (float) Booking::where('status', BookingStatus::Confirmed->value)->sum('total_price');

        return Inertia::render('Payments/Index', [
            'bookings' => $bookings->paginate(10, ['*'], 'bookings')->withQueryString(),
            'events' => $events,
            'alerts' => $alerts->summarize(),
            'revenue' => $revenue,
            'filters' => $request->only('method', 'status', 'search'),
        ]);
    }
}
