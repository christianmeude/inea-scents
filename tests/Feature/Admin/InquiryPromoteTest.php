<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Inquiry;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryPromoteTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    private function inquiry(array $o = []): Inquiry
    {
        return Inquiry::create(array_merge(['name' => 'Maria Clara', 'email' => 'maria@example.com', 'phone' => '+639171234567', 'event_date' => '2026-12-15', 'message' => 'Hi.'], $o));
    }

    private function pkg(): Package
    {
        return Package::create(['name' => 'Signature', 'description' => 'Test', 'price' => 2500]);
    }

    private function tieredPkg(): Package
    {
        return Package::create([
            // Distinct from the seeded real offering: names are unique now.
            'name' => 'Tiered Test Bar',
            'description' => 'Test tiers',
            'price' => 4499,
            'pax_options' => [50, 70, 100, 150],
            'pax_prices' => [50 => 4499, 70 => 6399, 100 => 8799, 150 => 13119],
        ]);
    }

    private function payload(Package $p, array $o = []): array
    {
        return array_merge(['package_id' => $p->id, 'pax' => 50, 'venue_address' => '123 Main St', 'payment_method' => 'cash'], $o);
    }

    public function test_promote_contacted_creates_prefilled_booking(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $p = $this->pkg();
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($p))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['inquiry_id' => $i->id, 'customer_name' => 'Maria Clara', 'customer_email' => 'maria@example.com', 'customer_phone' => '+639171234567', 'event_date' => '2026-12-15', 'total_price' => 2500]);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'booked']);
    }

    public function test_promote_dateless_without_date_rejected(): void
    {
        $i = $this->inquiry(['status' => 'contacted', 'event_date' => null]);
        $r = $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg()));
        $r->assertSessionHasErrors('event_date');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'contacted']);
    }

    public function test_promote_dateless_with_available_date_succeeds(): void
    {
        $i = $this->inquiry(['status' => 'contacted', 'event_date' => null]);
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg(), ['event_date' => '2027-01-20']))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['inquiry_id' => $i->id, 'event_date' => '2027-01-20']);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'booked']);
    }

    public function test_promote_unavailable_date_rejected(): void
    {
        $p = $this->pkg();
        Booking::create(['customer_name' => 'Taken', 'customer_email' => 'taken@example.com', 'pax' => 10, 'package_id' => $p->id, 'event_date' => '2026-12-15', 'venue_address' => '1 Taken St', 'status' => 'Confirmed', 'payment_method' => 'cash']);
        $i = $this->inquiry(['status' => 'contacted']);
        $r = $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($p));
        $r->assertSessionHasErrors('event_date');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_inquiry_stays_booked_when_booking_cancelled(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg()))->assertRedirect();
        Booking::first()->update(['status' => 'Cancelled']);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'booked']);
    }

    public function test_second_promote_rejected(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $p = $this->pkg();
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($p))->assertRedirect();
        $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($p))->assertSessionHasErrors();
        $this->assertEquals(1, Booking::count());
    }

    public function test_promote_requires_contacted_status(): void
    {
        $i = $this->inquiry();
        $r = $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg()));
        $r->assertSessionHasErrors();
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_non_admin_cannot_promote(): void
    {
        $u = User::factory()->create(['is_admin' => false]);
        $this->actingAs($u)->post(route('admin.inquiries.promote', $this->inquiry(['status' => 'contacted'])), $this->payload($this->pkg()))->assertNotFound();
    }

    public function test_promote_with_blank_event_time_succeeds(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg(), ['event_time' => '']))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['inquiry_id' => $i->id, 'event_time' => null]);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'booked']);
    }

    public function test_promote_with_time_slot_label_converts_to_start_time(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg(), ['event_time' => '2:00 PM - 5:00 PM']))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['inquiry_id' => $i->id, 'event_time' => '14:00:00']);
    }

    public function test_promote_with_garbage_event_time_rejected(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $r = $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg(), ['event_time' => 'sometime-ish']));
        $r->assertSessionHasErrors('event_time');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'contacted']);
    }

    public function test_promote_with_blank_pax_rejected(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $r = $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($this->pkg(), ['pax' => '']));
        $r->assertSessionHasErrors('pax');
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseHas('inquiries', ['id' => $i->id, 'status' => 'contacted']);
    }

    public function test_promote_with_off_tier_pax_rejected(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $r = $this->actingAs($this->admin)->from(route('admin.inquiries.show', $i))->post(route('admin.inquiries.promote', $i), $this->payload($this->tieredPkg(), ['pax' => 51]));
        $r->assertSessionHasErrors('pax');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_promote_with_tier_pax_uses_tier_price(): void
    {
        $i = $this->inquiry(['status' => 'contacted']);
        $this->actingAs($this->admin)->post(route('admin.inquiries.promote', $i), $this->payload($this->tieredPkg(), ['pax' => 70]))->assertRedirect();
        $this->assertDatabaseHas('bookings', ['inquiry_id' => $i->id, 'pax' => 70, 'total_price' => 6399]);
    }
}
