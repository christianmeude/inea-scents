<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('package')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('customer_name', 'like', "%{$search}%")
                ->orWhere('booking_reference', 'like', "%{$search}%");
        }

        $bookings = $query->paginate(10)->withQueryString();
        $packages = Package::all(['id', 'name']);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'packages' => $packages,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request, \App\Actions\CreateBooking $createBooking)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'package_id' => 'required|exists:packages,id',
            'pax' => 'nullable|integer|min:1',
            'event_date' => 'required|date',
            'event_time' => 'nullable',
            'venue_address' => 'required|string|max:255',
            'payment_method' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\PaymentMethod::class)],
            'status' => 'required|string|in:Confirmed,Pending,Unavailable,Cancelled',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $createBooking->execute($validated);

        return redirect()->route('admin.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function update(Request $request, Booking $booking, \App\Actions\UpdateBooking $updateBooking)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'package_id' => 'required|exists:packages,id',
            'pax' => 'nullable|integer|min:1',
            'event_date' => 'required|date',
            'event_time' => 'nullable',
            'venue_address' => 'required|string|max:255',
            'payment_method' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\PaymentMethod::class)],
            'status' => 'required|string|in:Confirmed,Pending,Unavailable,Cancelled',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $updateBooking->execute($booking, $validated);

        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function approve(Booking $booking)
    {
        // Capitalized because the validation rules in store/update use Title Case
        $booking->update(['status' => \App\Enums\BookingStatus::Confirmed->value]);

        return back()->with('success', 'Booking approved successfully.');
    }
}
