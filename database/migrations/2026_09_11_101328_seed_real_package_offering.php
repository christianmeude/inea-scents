<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace mock seed packages with the single real offering.
     *
     * Runs after the pax_prices schema migration. Destroys mock rows (and,
     * via cascade, any bookings referencing them — prod holds only synthetic
     * test traces at this point, which are slated for cleanup anyway).
     * Deterministic by name: safe to re-run.
     */
    public function up(): void
    {
        $tiers = [
            50 => 4499.00,
            70 => 6399.00,
            100 => 8799.00,
            150 => 13119.00,
        ];

        DB::table('packages')->delete();

        DB::table('packages')->updateOrInsert(
            ['name' => 'Essential 10ml Perfume Bar'],
            [
                'description' => 'Perfume bar service starting at Php 4,499.00. One booking lasts 3–4 hrs.',
                'price' => min($tiers),
                'inclusions' => json_encode([
                    'Featuring your logo and a hemp cord',
                    '4 inspired scents',
                    'Perfume Bar set up',
                    'Claim Stub',
                    'Duration: 3 hrs to 4 hrs',
                    '2 Staff Members',
                ]),
                'pax_options' => json_encode(array_keys($tiers)),
                'pax_prices' => json_encode($tiers),
                'freebies' => json_encode([
                    'Selfie Mirror',
                    '1 gift for celebrant',
                ]),
                'images' => json_encode([]),
                'gallery_images' => json_encode([]),
                'rating' => 0,
                'reviews_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('packages')->where('name', 'Essential 10ml Perfume Bar')->delete();
    }
};
