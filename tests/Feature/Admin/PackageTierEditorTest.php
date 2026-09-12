<?php

namespace Tests\Feature\Admin;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageTierEditorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->package = Package::create([
            'name' => 'Essential',
            'description' => 'Test',
            'price' => 1000,
            'pax_options' => [50],
            'pax_prices' => [50 => 4499],
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Essential',
            'description' => 'Test',
            'price' => 1000,
            'inclusions' => [],
            'pax_options' => [],
            'freebies' => [],
            'pax_prices' => ['50' => 4499, '70' => 6399],
        ], $overrides);
    }

    public function test_update_persists_tier_map_and_derives_options_and_starts_at()
    {
        $this->actingAs($this->admin)->put(
            route('admin.packages.update', $this->package->id),
            $this->payload(['pax_prices' => ['100' => 8799, '50' => 4499, '150' => 13119]])
        )->assertRedirect(route('admin.packages.index'));

        $package = $this->package->fresh();
        $this->assertEquals([50 => 4499, 100 => 8799, 150 => 13119], $package->pax_prices);
        $this->assertSame([50, 100, 150], $package->pax_options);
        $this->assertSame('4499.00', (string) $package->price);
    }

    public function test_update_drops_invalid_tier_pairs()
    {
        $this->actingAs($this->admin)->put(
            route('admin.packages.update', $this->package->id),
            $this->payload(['pax_prices' => ['50' => 4499, '0' => 10, 'abc' => 20, '70' => -5, '100' => '']])
        )->assertRedirect(route('admin.packages.index'));

        $package = $this->package->fresh();
        $this->assertEquals([50 => 4499], $package->pax_prices);
        $this->assertSame([50], $package->pax_options);
    }

    public function test_update_without_map_keeps_legacy_options_and_price()
    {
        $this->actingAs($this->admin)->put(
            route('admin.packages.update', $this->package->id),
            $this->payload(['pax_options' => [30, 60], 'price' => 2500, 'pax_prices' => []])
        )->assertRedirect(route('admin.packages.index'));

        $package = $this->package->fresh();
        $this->assertSame([], $package->pax_prices);
        $this->assertSame([30, 60], $package->pax_options);
        $this->assertSame('2500.00', (string) $package->price);
    }

    public function test_store_persists_tier_map()
    {
        $this->actingAs($this->admin)->post(
            route('admin.packages.store'),
            $this->payload(['name' => 'Deluxe'])
        )->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseHas('packages', ['name' => 'Deluxe']);
        $package = Package::where('name', 'Deluxe')->first();
        $this->assertSame([50, 70], $package->pax_options);
        $this->assertSame('4499.00', (string) $package->price);
    }

    public function test_guest_cannot_update_tiers()
    {
        $this->put(route('admin.packages.update', $this->package->id), $this->payload())
            ->assertRedirect('/admin/login');
    }
}
