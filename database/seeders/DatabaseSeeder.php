<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a Super Admin User
        $user = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Super Admin'),
                'password' => bcrypt(env('ADMIN_PASSWORD', 'password')),
                'is_admin' => true,
            ]
        );

        // 2. Create Scents
        $scentsData = [
            ['name' => 'Lavender Dream', 'description' => 'A calming, floral lavender aroma.'],
            ['name' => 'Vanilla Bean', 'description' => 'Sweet, warm, and comforting vanilla.'],
            ['name' => 'Ocean Breeze', 'description' => 'Crisp, clean, and refreshing marine notes.'],
            ['name' => 'Citrus Burst', 'description' => 'Energizing orange, lemon, and grapefruit.'],
            ['name' => 'Sandalwood Spice', 'description' => 'Earthy, woody, and slightly spicy.'],
        ];

        $scents = [];
        foreach ($scentsData as $data) {
            $scents[] = \App\Models\Scent::create($data);
        }

        // 3. Create Packages — single real offering with pax tiers.
        // Prices live in pax_prices; scalar price is the "starts at" base.
        $tiers = [50 => 4499.00, 70 => 6399.00, 100 => 8799.00, 150 => 13119.00];
        $package = \App\Models\Package::create([
            'name' => 'Essential 10ml Perfume Bar',
            'description' => 'Perfume bar service starting at Php 4,499.00. One booking lasts 3–4 hrs.',
            'price' => min($tiers),
            'inclusions' => [
                'Featuring your logo and a hemp cord',
                '4 inspired scents',
                'Perfume Bar set up',
                'Claim Stub',
                'Duration: 3 hrs to 4 hrs',
                '2 Staff Members',
            ],
            'pax_options' => array_keys($tiers),
            'pax_prices' => $tiers,
            'freebies' => ['Selfie Mirror', '1 gift for celebrant'],
            'images' => [],
            'gallery_images' => [],
            'rating' => 0.00, // Unrated
        ]);

        // 4. Attach Scents to Package — the frozen 4 included scents.
        $package->scents()->attach([$scents[0]->id, $scents[1]->id, $scents[2]->id, $scents[3]->id]);

        // 5. Create some Bookings
        $booking1 = \App\Models\Booking::create([
            'booking_reference' => 'BOOKING-TEST01',
            'package_id' => $package->id,
            'customer_name' => 'Alice Wonderland',
            'customer_email' => 'alice@example.com',
            'pax' => 50,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'event_time' => '14:00:00',
            'venue_address' => 'Scent Studio A',
            'status' => 'Confirmed',
            'payment_method' => 'online',
            'total_price' => $tiers[50],
        ]);
        $booking1->scents()->attach([$scents[0]->id, $scents[1]->id]);

        $booking2 = \App\Models\Booking::create([
            'booking_reference' => 'BOOKING-TEST02',
            'package_id' => $package->id,
            'customer_name' => 'Bob Builder',
            'customer_email' => 'bob@example.com',
            'pax' => 70,
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'event_time' => '10:00:00',
            'venue_address' => 'Scent Studio B',
            'status' => 'Pending',
            'payment_method' => 'cash',
            'total_price' => $tiers[70],
        ]);
        $booking2->scents()->attach([$scents[0]->id]);
    }
}
