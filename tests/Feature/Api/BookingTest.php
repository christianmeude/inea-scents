<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Scent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_their_bookings(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 100,
        ]);

        $booking1 = Booking::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'customer_name' => 'John Doe',
            'event_date' => '2026-10-10',
            'venue_address' => '123 Test',
        ]);

        $booking2 = Booking::create([
            'user_id' => $otherUser->id,
            'package_id' => $package->id,
            'customer_name' => 'Jane Doe',
            'event_date' => '2026-10-10',
            'venue_address' => '123 Test',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/bookings');

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has(1)
                ->has('0', fn (AssertableJson $json) => $json->where('id', $booking1->id)
                    ->where('user_id', $user->id)
                    ->has('scents')
                    ->has('package')
                    ->etc()
                )
            );
    }

    public function test_unauthenticated_user_cannot_get_bookings(): void
    {
        $response = $this->getJson('/api/bookings');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_booking_with_scents(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Test Package',
            'description' => 'Desc',
            'price' => 100,
        ]);
        $scent1 = Scent::create(['name' => 'Scent 1', 'description' => 'Desc']);
        $scent2 = Scent::create(['name' => 'Scent 2', 'description' => 'Desc']);

        $bookingData = [
            'package_id' => $package->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '1234567890',
            'pax' => 50,
            'event_date' => '2026-12-01',
            'event_time' => '18:00:00',
            'venue_address' => '123 Main St',
            'payment_method' => 'credit_card',
            'scent_ids' => [$scent1->id, $scent2->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/bookings', $bookingData);

        $response->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) => $json->where('user_id', $user->id)
                ->where('customer_name', 'John Doe')
                ->where('payment_method', 'credit_card')
                ->has('scents', 2)
                ->etc()
            );

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'customer_name' => 'John Doe',
            'payment_method' => 'credit_card',
        ]);

        $booking = Booking::first();
        $this->assertCount(2, $booking->scents);
    }

    public function test_unauthenticated_user_cannot_create_booking(): void
    {
        $response = $this->postJson('/api/bookings', []);
        $response->assertStatus(401);
    }
}
