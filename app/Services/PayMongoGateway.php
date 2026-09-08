<?php

namespace App\Services;

use App\Exceptions\PaymentLinkFailedException;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;

class PayMongoGateway
{
    /**
     * Create a PayMongo payment link for the booking.
     *
     * The amount is derived from the booking's server-side total, never
     * from client input.
     *
     * @throws PaymentLinkFailedException
     */
    public function createLink(Booking $booking): string
    {
        try {
            $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
                ->post('https://api.paymongo.com/v1/links', [
                    'data' => [
                        'attributes' => [
                            'amount' => (int) ($booking->total_price * 100),
                            'description' => 'Inea Scents Booking - ' . $booking->booking_reference,
                            'remarks' => $booking->booking_reference,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                throw new \RuntimeException('PayMongo link request failed with status ' . $response->status());
            }

            return $response->json('data.attributes.checkout_url');
        } catch (\Throwable $e) {
            throw PaymentLinkFailedException::providerUnreachable();
        }
    }
}
