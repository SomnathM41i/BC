<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryAdminTest extends TestCase
{
    use RefreshDatabase;

    private function inquiry(): Inquiry
    {
        return Inquiry::create([
            'name' => 'Sunil Patil',
            'phone' => '9876543210',
            'service' => '3d-design',
            'message' => 'I want a new home in Atpadi.',
            'status' => Inquiry::STATUS_NEW,
        ]);
    }

    public function test_guests_cannot_read_enquiries(): void
    {
        $this->get('/admin/inquiries')->assertRedirect('/admin/login');
    }

    public function test_owner_sees_enquiries_and_the_new_count(): void
    {
        $this->inquiry();

        $this->actingAs($this->owner())
            ->get('/admin/inquiries')
            ->assertOk()
            ->assertSee('Sunil Patil')
            ->assertSee('I want a new home in Atpadi.')
            ->assertSee('3D Design')
            ->assertSee('Mark as read');
    }

    public function test_owner_can_update_status_but_not_to_an_unknown_value(): void
    {
        $inquiry = $this->inquiry();
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post("/admin/inquiries/{$inquiry->id}/status", ['status' => Inquiry::STATUS_READ])
            ->assertRedirect();

        $this->assertSame(Inquiry::STATUS_READ, $inquiry->fresh()->status);

        $this->actingAs($owner)
            ->post("/admin/inquiries/{$inquiry->id}/status", ['status' => 'deleted'])
            ->assertSessionHasErrors('status');
    }

    public function test_missing_pages_are_branded_in_the_language_of_the_url(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('Page Not Found');
        $this->get('/mr/no-such-page')->assertNotFound()->assertSee('पान सापडले नाही');
    }

    public function test_project_pages_have_one_share_image_and_a_plain_phone_number_in_schema(): void
    {
        \App\Models\PortfolioProject::create([
            'title' => 'Test Home',
            'summary' => 'A test home.',
            'description' => 'A test home.',
            'slug' => 'test-home',
            'category' => 'residential',
            'cover_image' => 'images/site/portfolio/terrace-residence/cover.jpg',
            'is_published' => true,
        ]);

        $html = $this->get('/projects/test-home')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'property="og:image"'));
        $this->assertStringContainsString('terrace-residence/cover.jpg', $html);
        $this->assertStringContainsString('"telephone":["+919021113887"]', $html);
    }
}
