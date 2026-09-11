<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
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

    public function test_guest_is_redirected_to_admin_login()
    {
        $this->get(route('admin.customers.index'))->assertRedirect('/admin/login');
    }

    public function test_non_admin_gets_not_found()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.customers.index'))->assertNotFound();
    }

    public function test_admin_can_view_customers_index_with_linked_and_guest_rows()
    {
        $linked = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $this->makeBooking(['user_id' => $linked->id]);
        $this->makeBooking(['customer_name' => 'Walk-in Guest', 'customer_email' => 'guest@example.com']);

        $response = $this->actingAs($this->user)->get(route('admin.customers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 2)
        );
    }

    public function test_admin_can_search_customers()
    {
        $this->makeBooking(['customer_email' => 'jane@example.com']);
        $this->makeBooking(['customer_name' => 'John Doe', 'customer_email' => 'john@example.com']);

        $response = $this->actingAs($this->user)->get(route('admin.customers.index', ['search' => 'jane']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Index')
            ->has('customers.data', 1)
            ->where('customers.data.0.email', 'jane@example.com')
        );
    }

    public function test_admin_can_view_customer_show_with_booking_and_inquiry_history()
    {
        $linked = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $this->makeBooking(['user_id' => $linked->id]);
        Inquiry::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+639171234567',
            'event_date' => '2026-12-15',
            'message' => 'Hi.',
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.customers.show', 'jane@example.com'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Customers/Show')
            ->where('customer.email', 'jane@example.com')
            ->has('customer.bookings', 1)
            ->has('customer.inquiries', 1)
            ->where('customer.user.email', 'jane@example.com')
        );
    }

    public function test_admin_can_link_booking_to_matching_account()
    {
        User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->user)
            ->from(route('admin.customers.show', 'jane@example.com'))
            ->post(route('admin.customers.link'), ['booking_id' => $booking->id]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => User::where('email', 'jane@example.com')->first()->id]);
    }

    public function test_link_fails_when_no_account_matches_email()
    {
        $booking = $this->makeBooking(['customer_email' => 'ghost@example.com']);

        $response = $this->actingAs($this->user)
            ->from(route('admin.customers.show', 'ghost@example.com'))
            ->post(route('admin.customers.link'), ['booking_id' => $booking->id]);

        $response->assertSessionHasErrors('booking_id');
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => null]);
    }

    public function test_admin_can_unlink_booking()
    {
        $linked = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $booking = $this->makeBooking(['user_id' => $linked->id]);

        $response = $this->actingAs($this->user)
            ->from(route('admin.customers.show', 'jane@example.com'))
            ->post(route('admin.customers.unlink'), ['booking_id' => $booking->id]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => null]);
    }

    public function test_updating_booking_email_auto_relinks_account()
    {
        $old = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $new = User::factory()->create(['is_admin' => false, 'email' => 'new@example.com']);
        $booking = $this->makeBooking(['user_id' => $old->id]);

        $response = $this->actingAs($this->user)->put(route('admin.bookings.update', $booking), [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'new@example.com',
            'customer_phone' => null,
            'package_id' => $this->package->id,
            'pax' => 10,
            'event_date' => '2026-10-24',
            'event_time' => null,
            'venue_address' => '123 Test St',
            'payment_method' => 'cash',
            'status' => 'Confirmed',
            'total_price' => null,
            'notes' => null,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => $new->id]);
    }

    public function test_updating_booking_email_to_unknown_clears_link()
    {
        $linked = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $booking = $this->makeBooking(['user_id' => $linked->id]);

        $this->actingAs($this->user)->put(route('admin.bookings.update', $booking), [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'unknown@example.com',
            'customer_phone' => null,
            'package_id' => $this->package->id,
            'pax' => 10,
            'event_date' => '2026-10-24',
            'event_time' => null,
            'venue_address' => '123 Test St',
            'payment_method' => 'cash',
            'status' => 'Confirmed',
            'total_price' => null,
            'notes' => null,
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => null]);
    }

    public function test_explicit_unlink_sticks_when_other_fields_change()
    {
        $linked = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);
        $booking = $this->makeBooking(['user_id' => $linked->id]);

        $this->actingAs($this->user)->post(route('admin.customers.unlink'), ['booking_id' => $booking->id]);

        $this->actingAs($this->user)->put(route('admin.bookings.update', $booking), [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'customer_phone' => null,
            'package_id' => $this->package->id,
            'pax' => 10,
            'event_date' => '2026-10-24',
            'event_time' => null,
            'venue_address' => '123 Test St',
            'payment_method' => 'cash',
            'status' => 'Confirmed',
            'total_price' => null,
            'notes' => 'Edited notes.',
        ]);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'user_id' => null]);
    }
}
