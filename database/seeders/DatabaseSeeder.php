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

        // 3. Create Packages
        $package1 = \App\Models\Package::create([
            'name' => 'Signature Perfume Workshop',
            'description' => 'A 2-hour guided session where you learn the basics of perfumery and create your own 30ml signature scent.',
            'price' => 1500.00,
            'inclusions' => ['30ml Perfume Bottle', 'Basic Ingredients', 'Gift Box'],
            'pax_options' => [1, 2, 3, 4],
            'freebies' => [],
            'images' => [],
            'gallery_images' => [],
            'rating' => 4.8,
        ]);

        $package2 = \App\Models\Package::create([
            'name' => 'Couples Scent Experience',
            'description' => 'An intimate 3-hour session for two. Craft complimentary scents for each other.',
            'price' => 3500.00,
            'inclusions' => ['Two 50ml Perfume Bottles', 'Premium Ingredients', 'Engraved Bottles', 'Champagne'],
            'pax_options' => [2],
            'freebies' => ['Polaroid Photo'],
            'images' => [],
            'gallery_images' => [],
            'rating' => 5.0,
        ]);

        $package3 = \App\Models\Package::create([
            'name' => 'Premium Custom Blend',
            'description' => 'Work 1-on-1 with a master perfumer to develop a high-end customized fragrance.',
            'price' => 5000.00,
            'inclusions' => ['100ml Premium Bottle', 'Rare Ingredients Access', 'Digital Recipe Card'],
            'pax_options' => [1],
            'freebies' => ['Travel size 10ml roller'],
            'images' => [],
            'gallery_images' => [],
            'rating' => 0.00, // Unrated
        ]);

        // 4. Attach Scents to Packages
        // Signature workshop gets basic scents
        $package1->scents()->attach([$scents[0]->id, $scents[1]->id, $scents[2]->id, $scents[3]->id]);
        
        // Couples gets all scents
        $package2->scents()->attach([$scents[0]->id, $scents[1]->id, $scents[2]->id, $scents[3]->id, $scents[4]->id]);
        
        // Premium gets exclusive scents (plus some basics)
        $package3->scents()->attach([$scents[4]->id, $scents[0]->id]);

        // 5. Create some Bookings
        $booking1 = \App\Models\Booking::create([
            'booking_reference' => 'BOOKING-TEST01',
            'package_id' => $package1->id,
            'customer_name' => 'Alice Wonderland',
            'customer_email' => 'alice@example.com',
            'pax' => 2,
            'event_date' => now()->addDays(5)->format('Y-m-d'),
            'event_time' => '14:00:00',
            'venue_address' => 'Scent Studio A',
            'status' => 'Confirmed',
            'payment_method' => 'credit_card',
            'total_price' => $package1->price * 2,
        ]);
        $booking1->scents()->attach([$scents[0]->id, $scents[1]->id]);

        $booking2 = \App\Models\Booking::create([
            'booking_reference' => 'BOOKING-TEST02',
            'package_id' => $package2->id,
            'customer_name' => 'Bob Builder',
            'customer_email' => 'bob@example.com',
            'pax' => 2,
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'event_time' => '10:00:00',
            'venue_address' => 'Scent Studio B',
            'status' => 'Pending',
            'payment_method' => 'cash',
            'total_price' => $package2->price,
        ]);
        $booking2->scents()->attach([$scents[4]->id]);
    }
}
