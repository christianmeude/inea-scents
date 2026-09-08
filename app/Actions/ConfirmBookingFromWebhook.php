<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

/**
 * Owns Pending → Confirmed transitions from verified webhooks.
 * The webhook stays the sole online confirmer: only Pending bookings
 * move, everything else is a no-op.
 */
class ConfirmBookingFromWebhook
{
    public function execute(string $bookingReference): ?Booking
    {
        $booking = Booking::where('booking_reference', $bookingReference)->first();

        if ($booking && $booking->status === BookingStatus::Pending) {
            $booking->update(['status' => BookingStatus::Confirmed]);
            Log::channel('webhook')->info("Booking {$bookingReference} confirmed via PayMongo webhook");

            return $booking->fresh();
        }

        return null;
    }
}
