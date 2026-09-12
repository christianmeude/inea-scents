<?php

namespace Tests\Feature\Api;

use App\Actions\CreateBookingWithCheckout;
use App\Exceptions\PaymentLinkFailedException;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function data(Package $package): array
    {
        return [
            'package_id' => $package->id,
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'pax' => 2,
            'event_date' => '2026-12-15',
            'event_time' => '14:00:00',
            'venue_address' => '789 Event Place',
            'payment_method' => 'online',
        ];
    }

    public function test_flow_returns_booking_with_checkout_url_on_success(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 3500,
        ]);

        Http::fake([
            'api.paymongo.com/*' => Http::response([
                'data' => ['attributes' => ['checkout_url' => 'https://checkout.paymongo.test/seam-1']],
            ], 200),
        ]);

        $booking = app(CreateBookingWithCheckout::class)->execute($this->data($package), $user);

        $this->assertSame('https://checkout.paymongo.test/seam-1', $booking->checkout_url);
        $this->assertSame(1, \App\Models\Booking::count());
    }

    public function test_flow_throws_and_leaves_zero_rows_on_link_failure(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 3500,
        ]);

        Http::fake(['api.paymongo.com/*' => Http::response(['errors' => [['detail' => 'boom']]], 500)]);

        $this->expectException(PaymentLinkFailedException::class);

        try {
            app(CreateBookingWithCheckout::class)->execute($this->data($package), $user);
        } finally {
            $this->assertDatabaseCount('bookings', 0);
        }
    }

    public function test_flow_leaves_zero_rows_on_provider_exception(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 3500,
        ]);

        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 60');
        });

        $this->expectException(PaymentLinkFailedException::class);

        try {
            app(CreateBookingWithCheckout::class)->execute($this->data($package), $user);
        } finally {
            $this->assertDatabaseCount('bookings', 0);
        }
    }

    public function test_offline_payment_skips_link_creation(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 3500,
        ]);

        Http::fake(['api.paymongo.com/*' => Http::response([], 500)]);

        $booking = app(CreateBookingWithCheckout::class)->execute(
            array_merge($this->data($package), ['payment_method' => 'cash']),
            $user
        );

        Http::assertNothingSent();
        $this->assertNull($booking->checkout_url);
    }
}
