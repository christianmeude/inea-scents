<?php

namespace App\Actions;

use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateBooking
{
    /**
     * Execute the booking update logic.
     *
     * @param Booking $booking
     * @param array $data
     * @return Booking
     * @throws ValidationException
     */
    public function execute(Booking $booking, array $data): Booking
    {
        // 1. Validate Blocked Dates & 1 Booking Per Day logic only if event_date changed
        if (isset($data['event_date']) && Carbon::parse($data['event_date'])->toDateString() !== Carbon::parse($booking->event_date)->toDateString()) {
            $dateStr = Carbon::parse($data['event_date'])->toDateString();
            
            $isBlocked = BlockedDate::whereDate('date', $dateStr)->exists();
            $isBooked = Booking::whereDate('event_date', $dateStr)
                ->where('id', '!=', $booking->id)
                ->where('status', '!=', \App\Enums\BookingStatus::Cancelled->value)
                ->exists();

            if ($isBlocked || $isBooked) {
                throw ValidationException::withMessages([
                    'event_date' => ['The selected date is marked as unavailable.'],
                ]);
            }
        }

        // 2. Update booking
        $booking->update($data);

        // 3. Attach scents if provided (override existing)
        if (isset($data['scent_ids'])) {
            $booking->scents()->sync($data['scent_ids']);
        }

        return $booking;
    }
}
