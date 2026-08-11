<?php

namespace Tests\Feature\Api;

use App\Models\Package;
use App\Models\Scent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_packages(): void
    {
        $package = Package::create([
            'name' => 'Romantic Getaway',
            'price' => 199.99,
            'rating' => 4.5,
            'reviews_count' => 120,
            'gallery_images' => ['image1.jpg', 'image2.jpg'],
        ]);

        $response = $this->getJson('/api/packages');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'price',
                    'rating',
                    'reviews_count',
                    'images',
                    'gallery_images',
                    'description',
                ],
            ])
            ->assertJsonFragment([
                'id' => $package->id,
                'rating' => '4.50',
            ]);
    }

    public function test_can_show_package_details(): void
    {
        $package = Package::create([
            'name' => 'Adventure Trip',
            'description' => 'A thrilling experience.',
            'price' => 299.99,
            'rating' => 4.8,
            'reviews_count' => 50,
            'gallery_images' => ['adv1.jpg'],
            'inclusions' => ['Food', 'Guide'],
        ]);

        $scent = Scent::create([
            'name' => 'Lavender',
            'description' => 'A relaxing scent.',
        ]);

        $package->scents()->attach($scent->id);

        $response = $this->getJson('/api/packages/'.$package->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'inclusions',
                'pax_options',
                'freebies',
                'price',
                'rating',
                'reviews_count',
                'images',
                'gallery_images',
                'created_at',
                'updated_at',
                'scents' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'image_url',
                        'is_available',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $package->id,
                'name' => 'Adventure Trip',
            ])
            ->assertJsonFragment([
                'name' => 'Lavender',
            ]);
    }

    public function test_returns_404_for_missing_package(): void
    {
        $response = $this->getJson('/api/packages/999');

        $response->assertStatus(404);
    }
}
