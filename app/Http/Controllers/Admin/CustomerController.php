<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = strtolower(trim((string) $request->input('search', '')));

        $users = User::where('is_admin', false)
            ->withCount('bookings')
            ->withMax('bookings as last_booking_at', 'created_at')
            ->get();

        $bookings = Booking::query()->orderByDesc('created_at')->get(['id', 'user_id', 'customer_name', 'customer_email', 'created_at']);
        $inquiries = Inquiry::query()->orderByDesc('created_at')->get(['id', 'name', 'email', 'created_at']);

        $rows = [];

        foreach ($users as $user) {
            $rows[self::key($user->email)] = [
                'email' => $user->email,
                'name' => $user->name,
                'user_id' => $user->id,
                'bookings_count' => $user->bookings_count,
                'inquiries_count' => 0,
                'last_activity_at' => $user->last_booking_at,
            ];
        }

        foreach ($bookings as $booking) {
            if (empty($booking->customer_email)) {
                continue;
            }
            $key = self::key($booking->customer_email);
            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'email' => $booking->customer_email,
                    'name' => $booking->customer_name,
                    'user_id' => null,
                    'bookings_count' => 0,
                    'inquiries_count' => 0,
                    'last_activity_at' => null,
                ];
            }
            // Bookings are newest-first, so the first hit wins the display name.
            if (empty($rows[$key]['name'])) {
                $rows[$key]['name'] = $booking->customer_name;
            }
            // Linked users count via withCount; only guest rows accumulate here.
            if ($rows[$key]['user_id'] === null) {
                $rows[$key]['bookings_count']++;
            }
            if ($rows[$key]['last_activity_at'] === null
                || $booking->created_at->gt($rows[$key]['last_activity_at'])) {
                $rows[$key]['last_activity_at'] = $booking->created_at;
            }
        }

        foreach ($inquiries as $inquiry) {
            if (empty($inquiry->email)) {
                continue;
            }
            $key = self::key($inquiry->email);
            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'email' => $inquiry->email,
                    'name' => $inquiry->name,
                    'user_id' => null,
                    'bookings_count' => 0,
                    'inquiries_count' => 0,
                    'last_activity_at' => null,
                ];
            }
            $rows[$key]['inquiries_count']++;
            if (empty($rows[$key]['name'])) {
                $rows[$key]['name'] = $inquiry->name;
            }
            if ($rows[$key]['last_activity_at'] === null
                || $inquiry->created_at->gt($rows[$key]['last_activity_at'])) {
                $rows[$key]['last_activity_at'] = $inquiry->created_at;
            }
        }

        $list = collect(array_values($rows));

        if ($search !== '') {
            $list = $list->filter(fn ($row) => str_contains(strtolower((string) $row['email']), $search)
                || str_contains(strtolower((string) $row['name']), $search))->values();
        }

        $list = $list->sortByDesc(fn ($row) => $row['last_activity_at'] ?? '')->values();

        $page = max(1, (int) $request->input('page', 1));
        $customers = new LengthAwarePaginator(
            $list->forPage($page, 10)->values(),
            $list->count(),
            10,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(string $email)
    {
        $email = urldecode($email);

        $user = User::where('email', $email)->where('is_admin', false)->first();

        $bookings = Booking::with('package')
            ->where(function ($query) use ($email, $user) {
                $query->where('customer_email', $email);
                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->latest()
            ->get();

        $inquiries = Inquiry::where('email', $email)->latest()->get();

        $name = $bookings->first()?->customer_name
            ?? $inquiries->first()?->name
            ?? $user?->name
            ?? $email;

        return Inertia::render('Customers/Show', [
            'customer' => [
                'email' => $email,
                'name' => $name,
                'user' => $user ? $user->only(['id', 'name', 'email']) : null,
                'bookings' => $bookings,
                'inquiries' => $inquiries,
            ],
        ]);
    }

    public function link(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        $customer = User::where('email', $booking->customer_email)
            ->where('is_admin', false)
            ->first();

        if (! $customer) {
            return back()->withErrors(['booking_id' => 'No customer account matches this booking email.']);
        }

        $booking->update(['user_id' => $customer->id]);

        return back()->with('success', 'Booking linked to customer account.');
    }

    public function unlink(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        Booking::findOrFail($validated['booking_id'])->update(['user_id' => null]);

        return back()->with('success', 'Booking unlinked from customer account.');
    }

    private static function key(?string $email): string
    {
        return strtolower(trim((string) $email));
    }
}
