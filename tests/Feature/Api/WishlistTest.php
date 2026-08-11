<?php

namespace Tests\Feature\Api;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_wishlist(): void
    {
        $response = $this->getJson('/api/wishlist');
        $response->assertStatus(401);

        $response = $this->postJson('/api/wishlist/toggle', [
            'package_id' => 1,
        ]);
        $response->assertStatus(401);
    }

    public function test_user_can_get_wishlist(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Romantic Getaway',
            'price' => 199.99,
            'rating' => 4.5,
            'reviews_count' => 120,
            'gallery_images' => ['image1.jpg', 'image2.jpg'],
        ]);
        
        $user->wishlistPackages()->attach($package->id);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wishlist');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $package->id,
            ]);
    }

    public function test_user_can_toggle_wishlist(): void
    {
        $user = User::factory()->create();
        $package = Package::create([
            'name' => 'Adventure Trip',
            'price' => 299.99,
            'rating' => 4.8,
            'reviews_count' => 50,
            'gallery_images' => ['adv1.jpg'],
        ]);

        Sanctum::actingAs($user);

        // Add to wishlist
        $response = $this->postJson('/api/wishlist/toggle', [
            'package_id' => $package->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'attached' => true,
                'message' => 'Package added to wishlist.',
            ]);

        $this->assertDatabaseHas('package_user_wishlist', [
            'user_id' => $user->id,
            'package_id' => $package->id,
        ]);

        // Remove from wishlist
        $response = $this->postJson('/api/wishlist/toggle', [
            'package_id' => $package->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'attached' => false,
                'message' => 'Package removed from wishlist.',
            ]);

        $this->assertDatabaseMissing('package_user_wishlist', [
            'user_id' => $user->id,
            'package_id' => $package->id,
        ]);
    }

    public function test_toggle_validates_package_id(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wishlist/toggle', [
            'package_id' => 999, // Non-existent package
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['package_id']);
    }
}
