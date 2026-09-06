<?php

namespace App\Actions;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use App\Enums\BookingStatus;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    /**
     * Execute the booking creation logic.
     *
     * @param array $data
     * @param User|null $user
     * @return Booking
     * @throws ValidationException
     */
    public function execute(array $data, ?User $user = null): Booking
    {
        // Sweep-first: a stale Pending must never block a new booking.
        Booking::expireStalePending();

        // 1. Validate Blocked Dates & 1 Booking Per Day logic
        $dateStr = Carbon::parse($data['event_date'])->toDateString();
        
        $isBlocked = BlockedDate::whereDate('date', $dateStr)->exists();
        $isBooked = Booking::whereDate('event_date', $dateStr)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->exists();

        if ($isBlocked || $isBooked) {
            throw ValidationException::withMessages([
                'event_date' => ['The selected date is marked as unavailable.'],
            ]);
        }

        // 2. Prepare defaults for missing data (Mobile API vs Admin differences)
        // Total is always derived server-side from the package price so the
        // client can never spoof the PayMongo charge amount (was never set,
        // producing amount 0 at link creation).
        $package = Package::findOrFail($data['package_id']);
        $bookingData = array_merge([
            'status' => BookingStatus::Pending->value,
            'total_price' => $package->price,
        ], $data);
        unset($bookingData['scent_ids']);
        
        // 3. Create booking and optionally associate with a user
        if ($user) {
            $booking = $user->bookings()->create($bookingData);
        } else {
            $booking = Booking::create($bookingData);
        }

        // 4. Attach scents if provided
        if (!empty($data['scent_ids'])) {
            $booking->scents()->attach($data['scent_ids']);
        }

        return $booking;
    }
}
