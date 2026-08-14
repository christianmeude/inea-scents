<?php

namespace Tests\Feature\Api;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_availability_for_a_given_month_and_year()
    {
        $package = Package::create([
            'name' => 'Signature',
            'description' => 'Test',
            'price' => 1000,
        ]);

        // Target month: August 2024
        // Blocked via BlockedDate
        BlockedDate::create(['date' => '2024-08-05']);

        // Blocked via Booking
        Booking::create([
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '1234567890',
            'package_id' => $package->id,
            'pax' => 50,
            'event_date' => '2024-08-10',
            'event_time' => '18:00',
            'venue_address' => '123 Test St',
            'status' => 'Confirmed',
            'total_price' => 500.00,
            'payment_method' => 'credit_card',
        ]);

        $response = $this->getJson('/api/availability?month=8&year=2024');

        $response->assertStatus(200)
            ->assertValidRequest()
            ->assertValidResponse(200);

        // It should return an array of all days in the month
        // 31 days in August
        $response->assertJsonCount(31);

        $response->assertJsonFragment([
            'date' => '2024-08-05',
            'status' => 'Booked',
        ]);

        $response->assertJsonFragment([
            'date' => '2024-08-10',
            'status' => 'Booked',
        ]);

        $response->assertJsonFragment([
            'date' => '2024-08-01',
            'status' => 'Available',
        ]);
    }

    public function test_it_defaults_to_current_month_and_year_if_not_provided()
    {
        $response = $this->getJson('/api/availability');

        $response->assertStatus(200)
            ->assertValidRequest()
            ->assertValidResponse(200);
        $currentMonthDays = Carbon::now()->daysInMonth;
        $response->assertJsonCount($currentMonthDays);
    }
}
