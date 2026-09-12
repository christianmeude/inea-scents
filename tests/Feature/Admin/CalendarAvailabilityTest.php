<?php

namespace Tests\Feature\Admin;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CalendarAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'booking_reference' => 'INEA-' . strtoupper(uniqid()),
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'pax' => 10,
            'package_id' => $this->package->id,
            'event_date' => '2026-10-24',
            'venue_address' => '123 Test St',
            'status' => 'Confirmed',
            'payment_method' => 'cash',
        ], $overrides));
    }

    public function test_index_shares_calculator_day_states()
    {
        $this->makeBooking(['event_date' => '2026-10-05']);
        $this->makeBooking(['event_date' => '2026-10-06', 'status' => 'Cancelled']);
        BlockedDate::create(['date' => '2026-10-07']);

        $response = $this->actingAs($this->admin)->get(route('admin.calendar.index', [
            'month' => 10, 'year' => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Calendar/Index')
            ->has('bookings.data', 1)
            ->where('availability.2026-10-05', 'Booked')
            ->where('availability.2026-10-06', 'Available')
            ->where('availability.2026-10-07', 'Booked')
            ->where('availability.2026-10-08', 'Available')
        );
    }

    public function test_year_mode_returns_twelve_months()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.calendar.index', [
            'view' => 'year', 'year' => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Calendar/Index')
            ->has('yearAvailability', 12)
        );
    }

    public function test_toggle_block_refuses_active_booking_but_allows_cancelled_date()
    {
        $this->makeBooking(['event_date' => '2026-10-05', 'status' => 'Confirmed']);
        $this->makeBooking(['event_date' => '2026-10-06', 'status' => 'Cancelled']);

        $this->actingAs($this->admin)->post(route('admin.calendar.toggle-block'), ['date' => '2026-10-05'])
            ->assertSessionHasErrors('date');
        $this->assertDatabaseMissing('blocked_dates', ['date' => '2026-10-05']);

        $this->actingAs($this->admin)->post(route('admin.calendar.toggle-block'), ['date' => '2026-10-06'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('blocked_dates', ['date' => '2026-10-06']);
    }

    public function test_guest_is_redirected_to_admin_login()
    {
        $this->get(route('admin.calendar.index'))->assertRedirect('/admin/login');
    }
}
