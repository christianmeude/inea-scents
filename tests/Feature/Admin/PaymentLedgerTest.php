<?php

namespace Tests\Feature\Admin;

use App\Actions\ConfirmBookingFromWebhook;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PaymentLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_admin' => true]);
        $this->package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'booking_reference' => 'INEA-'.strtoupper(uniqid()),
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'pax' => 10,
            'package_id' => $this->package->id,
            'event_date' => '2026-10-24',
            'venue_address' => '123 Test St',
            'status' => 'Confirmed',
            'total_price' => 1500,
            'payment_method' => 'online',
        ], $overrides));
    }

    public function test_guest_is_redirected_to_admin_login()
    {
        $this->get(route('admin.payments.index'))->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_not_found()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.payments.index'))->assertNotFound();
    }

    public function test_admin_can_view_ledger_with_bookings_events_and_alerts()
    {
        $booking = $this->makeBooking();
        DB::table('webhook_events')->insert([
            'event_id' => 'evt_test_123',
            'event_type' => 'link.payment.paid',
            'payload' => json_encode(['data' => ['attributes' => ['data' => ['attributes' => ['remarks' => $booking->booking_reference]]]]]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.payments.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Payments/Index')
            ->has('bookings.data', 1)
            ->where('bookings.data.0.payment_method', 'online')
            ->has('events.data', 1)
            ->where('events.data.0.booking_reference', $booking->booking_reference)
            ->has('alerts.rejectedCount')
            ->has('alerts.ignoredCount')
            ->has('alerts.unmatchedCount')
            ->where('revenue', 1500)
        );
    }

    public function test_admin_can_filter_ledger_by_method_status_and_search()
    {
        $this->makeBooking(['booking_reference' => 'INEA-CARD', 'payment_method' => 'online', 'status' => 'Confirmed']);
        $this->makeBooking(['booking_reference' => 'INEA-CASH', 'payment_method' => 'cash', 'status' => 'Pending', 'customer_name' => 'John Doe']);

        $response = $this->actingAs($this->user)->get(route('admin.payments.index', ['method' => 'cash']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('bookings.data', 1)
            ->where('bookings.data.0.booking_reference', 'INEA-CASH')
        );

        $response = $this->actingAs($this->user)->get(route('admin.payments.index', ['status' => 'Confirmed']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('bookings.data', 1)
            ->where('bookings.data.0.booking_reference', 'INEA-CARD')
        );

        $response = $this->actingAs($this->user)->get(route('admin.payments.index', ['search' => 'INEA-CARD']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('bookings.data', 1)
            ->where('bookings.data.0.booking_reference', 'INEA-CARD')
        );
    }

    public function test_paid_event_with_no_matching_booking_logs_warning()
    {
        $handler = new \Monolog\Handler\TestHandler();
        Log::channel('webhook')->pushHandler($handler);

        $result = (new ConfirmBookingFromWebhook)->execute('INEA-GHOST');

        $this->assertNull($result);
        $this->assertTrue($handler->hasWarningThatContains('webhook no-match'));
    }
}
