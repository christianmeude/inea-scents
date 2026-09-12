<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_bookings_index()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);
        Booking::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'test@example.com',
            'pax' => 10,
            'package_id' => $package->id,
            'event_date' => '2026-10-24',
            'venue_address' => '123 Test St',
            'status' => 'Confirmed',
            'payment_method' => 'online',
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.bookings.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Bookings/Index')
            ->has('bookings.data', 1)
            ->has('packages')
        );
    }

    public function test_admin_can_search_bookings()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);
        Booking::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'pax' => 5,
            'package_id' => $package->id,
            'event_date' => '2026-10-24',
            'venue_address' => '123 Test St',
            'payment_method' => 'online',
        ]);
        Booking::create([
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'pax' => 2,
            'package_id' => $package->id,
            'event_date' => '2026-11-24',
            'venue_address' => '456 Another St',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.bookings.index', ['search' => 'Jane']));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Bookings/Index')
            ->has('bookings.data', 1)
            ->where('bookings.data.0.customer_name', 'Jane Smith')
        );
    }

    public function test_admin_can_create_a_booking()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.bookings.store'), [
            'customer_name' => 'New Customer',
            'customer_email' => 'new@example.com',
            'package_id' => $package->id,
            'pax' => 100,
            'event_date' => '2026-12-01',
            'event_time' => '14:00',
            'venue_address' => '789 Party Ave',
            'total_price' => 1500.00,
            'status' => 'Confirmed',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'New Customer',
            'customer_email' => 'new@example.com',
            'pax' => 100,
            'status' => 'Confirmed',
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking->booking_reference);
        $this->assertStringStartsWith('BOOKING-', $booking->booking_reference);
    }

    public function test_admin_creating_booking_links_existing_customer_by_email()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);
        $customer = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $this->actingAs($this->user)->post(route('admin.bookings.store'), [
            'customer_name' => 'Returning Customer',
            'customer_email' => 'customer@example.com',
            'package_id' => $package->id,
            'pax' => 2,
            'event_date' => '2026-12-02',
            'venue_address' => '789 Party Ave',
            'status' => 'Pending',
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('bookings', [
            'customer_email' => 'customer@example.com',
            'user_id' => $customer->id,
        ]);
    }

    public function test_admin_creating_booking_for_unknown_email_leaves_user_unlinked()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);

        $this->actingAs($this->user)->post(route('admin.bookings.store'), [
            'customer_name' => 'Walk-in Guest',
            'customer_email' => 'walkin@example.com',
            'package_id' => $package->id,
            'pax' => 1,
            'event_date' => '2026-12-03',
            'venue_address' => '789 Party Ave',
            'status' => 'Pending',
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('bookings', [
            'customer_email' => 'walkin@example.com',
            'user_id' => null,
        ]);
    }

    public function test_admin_cannot_create_booking_with_online_payment()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.bookings.store'), [
            'customer_name' => 'PayMongo Customer',
            'customer_email' => 'online@example.com',
            'package_id' => $package->id,
            'pax' => 1,
            'event_date' => '2026-12-04',
            'venue_address' => '789 Party Ave',
            'status' => 'Pending',
            'payment_method' => 'online',
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseMissing('bookings', [
            'customer_email' => 'online@example.com',
        ]);
    }

    public function test_admin_cannot_create_booking_with_retired_bank_transfer()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);

        $response = $this->actingAs($this->user)->post(route('admin.bookings.store'), [
            'customer_name' => 'Legacy Customer',
            'customer_email' => 'legacy@example.com',
            'package_id' => $package->id,
            'pax' => 1,
            'event_date' => '2026-12-05',
            'venue_address' => '789 Party Ave',
            'status' => 'Pending',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseMissing('bookings', [
            'customer_email' => 'legacy@example.com',
        ]);
    }

    public function test_admin_can_update_a_booking_status()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);
        $booking = Booking::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'test@example.com',
            'pax' => 1,
            'package_id' => $package->id,
            'event_date' => '2026-10-24',
            'venue_address' => '123 Test St',
            'status' => 'Pending',
            'payment_method' => 'online',
        ]);

        $response = $this->actingAs($this->user)->put(route('admin.bookings.update', $booking), [
            'status' => 'Confirmed',
            'customer_name' => 'John Doe',
            'customer_email' => 'test@example.com',
            'pax' => 1,
            'package_id' => $package->id,
            'event_date' => '2026-10-24',
            'venue_address' => '123 Test St',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'Confirmed',
        ]);
    }
}
