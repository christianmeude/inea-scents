<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTrustTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'whsec_test_123';

    private function payload(string $reference, string $type = 'link.payment.paid', string $eventId = 'evt_test_1'): array
    {
        return [
            'data' => [
                'id' => $eventId,
                'attributes' => [
                    'type' => $type,
                    'data' => ['attributes' => ['remarks' => $reference]],
                ],
            ],
        ];
    }

    /** Returns [raw body, signature header] for a payload. */
    private function sign(array $payload, ?string $secret = null): array
    {
        $raw = json_encode($payload);
        $timestamp = (string) time();
        $sig = hash_hmac('sha256', $timestamp . '.' . $raw, $secret ?? $this->secret);

        return [$raw, "t={$timestamp},te={$sig},li={$sig}"];
    }

    private function pendingBooking(): Booking
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 3500,
        ]);

        return Booking::create([
            'user_id' => $user->id,
            'booking_reference' => 'IN-TEST-001',
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'package_id' => $package->id,
            'pax' => 2,
            'event_date' => '2026-12-15',
            'venue_address' => '789 Event Place',
            'payment_method' => 'credit_card',
            'status' => 'Pending',
            'total_price' => 3500,
        ]);
    }

    private function postWebhook(string $raw, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->call(
            'POST',
            '/api/webhooks/paymongo',
            [],
            [],
            [],
            array_merge(
                ['CONTENT_TYPE' => 'application/json'],
                $headers
            ),
            $raw
        );
    }

    public function test_verified_paid_event_confirms_pending_booking(): void
    {
        config(['services.paymongo.webhook_secret' => $this->secret]);
        $booking = $this->pendingBooking();
        [$raw, $header] = $this->sign($this->payload($booking->booking_reference));

        $response = $this->postWebhook($raw, ['HTTP_PAYMONGO_SIGNATURE' => $header]);

        $response->assertStatus(200);
        $this->assertSame('Confirmed', $booking->fresh()->status->value);
        $this->assertDatabaseHas('webhook_events', ['event_id' => 'evt_test_1']);
    }

    public function test_tampered_payload_rejected_before_any_db_write(): void
    {
        config(['services.paymongo.webhook_secret' => $this->secret]);
        $booking = $this->pendingBooking();
        [$raw, $header] = $this->sign($this->payload($booking->booking_reference));
        $tampered = str_replace('IN-TEST-001', 'IN-TEST-999', $raw);

        $response = $this->postWebhook($tampered, ['HTTP_PAYMONGO_SIGNATURE' => $header]);

        $response->assertStatus(401);
        $this->assertSame('Pending', $booking->fresh()->status->value);
        $this->assertDatabaseCount('webhook_events', 0);
    }

    public function test_missing_signature_rejected_before_any_db_write(): void
    {
        config(['services.paymongo.webhook_secret' => $this->secret]);
        $booking = $this->pendingBooking();
        [$raw] = $this->sign($this->payload($booking->booking_reference));

        $response = $this->postWebhook($raw);

        $response->assertStatus(401);
        $this->assertSame('Pending', $booking->fresh()->status->value);
        $this->assertDatabaseCount('webhook_events', 0);
    }

    public function test_duplicate_delivery_confirms_exactly_once(): void
    {
        config(['services.paymongo.webhook_secret' => $this->secret]);
        $booking = $this->pendingBooking();
        [$raw, $header] = $this->sign($this->payload($booking->booking_reference));

        $this->postWebhook($raw, ['HTTP_PAYMONGO_SIGNATURE' => $header])->assertStatus(200);
        $response = $this->postWebhook($raw, ['HTTP_PAYMONGO_SIGNATURE' => $header]);

        $response->assertStatus(200)->assertJson(['message' => 'Event already processed']);
        $this->assertSame('Confirmed', $booking->fresh()->status->value);
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('webhook_events')->count());
    }

    public function test_unknown_event_type_logged_and_ignored(): void
    {
        config(['services.paymongo.webhook_secret' => $this->secret]);
        $booking = $this->pendingBooking();
        [$raw, $header] = $this->sign($this->payload($booking->booking_reference, 'link.updated'));

        $response = $this->postWebhook($raw, ['HTTP_PAYMONGO_SIGNATURE' => $header]);

        $response->assertStatus(200);
        $this->assertSame('Pending', $booking->fresh()->status->value);
        $this->assertDatabaseCount('webhook_events', 0);
    }

    public function test_malformed_payload_rejected_without_db_write(): void
    {
        config(['services.paymongo.webhook_secret' => $this->secret]);
        [$raw, $header] = $this->sign(['data' => ['attributes' => ['type' => 'link.payment.paid']]]);

        $response = $this->postWebhook($raw, ['HTTP_PAYMONGO_SIGNATURE' => $header]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('webhook_events', 0);
    }
}
