<?php

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Exceptions\PaymentLinkFailedException;
use App\Models\Booking;
use App\Models\User;
use App\Services\PayMongoGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBookingWithCheckout
{
    public function __construct(
        private CreateBooking $createBooking,
        private PayMongoGateway $gateway,
    ) {}

    /**
     * Create a booking and, for online payment, its payment link atomically.
     *
     * Either returns a booking with a usable link or leaves no booking
     * behind — the transaction rolls back creation, scent pivots, and the
     * link update together. Shared by the mobile API and admin creation.
     *
     * @throws \Illuminate\Validation\ValidationException on unavailable date
     * @throws PaymentLinkFailedException when the link cannot be created
     */
    public function execute(array $data, ?User $user = null): Booking
    {
        return DB::transaction(function () use ($data, $user) {
            $booking = $this->createBooking->execute($data, $user);

            if ($booking->payment_method === PaymentMethod::CREDIT_CARD) {
                try {
                    $booking->update([
                        'checkout_url' => $this->gateway->createLink($booking),
                    ]);
                } catch (PaymentLinkFailedException $e) {
                    Log::warning('PayMongo link creation failed; booking rolled back.', [
                        'booking_reference' => $booking->booking_reference,
                    ]);

                    throw $e;
                }
            }

            return $booking;
        });
    }
}
