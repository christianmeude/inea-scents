<?php

namespace Tests\Feature\Admin;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    private function inquiry(array $overrides = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'name' => 'Maria Clara',
            'email' => 'maria@example.com',
            'phone' => '+639171234567',
            'event_date' => '2026-12-15',
            'message' => 'Garden wedding quote.',
        ], $overrides));
    }

    public function test_admin_can_list_inquiries(): void
    {
        $this->inquiry();

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Inquiries/Index'));
    }

    public function test_list_hides_archived_by_default(): void
    {
        $this->inquiry(['archived' => true]);
        $visible = $this->inquiry(['email' => 'jose@example.com']);

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('inquiries.data', fn ($data) => count($data) === 1 && $data[0]['id'] === $visible->id));
    }

    public function test_list_sorts_dateless_last(): void
    {
        $dateless = $this->inquiry(['email' => 'dateless@example.com', 'event_date' => null]);
        $dated = $this->inquiry(['email' => 'dated@example.com', 'event_date' => '2026-10-10']);

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('inquiries.data.0.id', $dated->id)
            ->where('inquiries.data.1.id', $dateless->id));
    }

    public function test_admin_can_view_inquiry(): void
    {
        $inquiry = $this->inquiry();

        $response = $this->actingAs($this->admin)->get(route('admin.inquiries.show', $inquiry));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('Inquiries/Show'));
    }

    public function test_admin_can_move_status_forward(): void
    {
        $inquiry = $this->inquiry();

        $response = $this->actingAs($this->admin)->put(route('admin.inquiries.update', $inquiry), [
            'status' => 'contacted',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'contacted']);
    }

    public function test_admin_can_skip_forward_to_closed(): void
    {
        $inquiry = $this->inquiry();

        $this->actingAs($this->admin)->put(route('admin.inquiries.update', $inquiry), [
            'status' => 'closed',
        ])->assertRedirect();

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'closed']);
    }

    public function test_backward_status_move_rejected(): void
    {
        $inquiry = $this->inquiry(['status' => 'contacted']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.inquiries.show', $inquiry))
            ->put(route('admin.inquiries.update', $inquiry), ['status' => 'new']);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'contacted']);
    }

    public function test_manual_move_to_booked_rejected(): void
    {
        $inquiry = $this->inquiry(['status' => 'contacted']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.inquiries.show', $inquiry))
            ->put(route('admin.inquiries.update', $inquiry), ['status' => 'booked']);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'contacted']);
    }

    public function test_archived_toggle_allowed_from_any_status(): void
    {
        $inquiry = $this->inquiry();

        $this->actingAs($this->admin)->put(route('admin.inquiries.update', $inquiry), [
            'archived' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'archived' => true, 'status' => 'new']);
    }

    public function test_non_admin_cannot_access(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $inquiry = $this->inquiry();

        $this->actingAs($user)->get(route('admin.inquiries.index'))->assertNotFound();
        $this->actingAs($user)->get(route('admin.inquiries.show', $inquiry))->assertNotFound();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.inquiries.index'))->assertRedirect('/admin/login');
    }
}
