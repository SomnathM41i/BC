<?php

namespace Tests\Feature;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use App\Models\PortfolioProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function enquiry(array $overrides = []): array
    {
        return [
            'name' => 'Test Visitor',
            'phone' => '9021113887',
            'email' => 'visitor@example.com',
            'service' => 'residential-construction',
            'message' => 'I would like to discuss building a house on my plot in Atpadi.',
            ...$overrides,
        ];
    }

    public function test_english_pages_render(): void
    {
        foreach (['/', '/about', '/services', '/projects', '/contact', '/credits'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_marathi_pages_render_with_marathi_content(): void
    {
        $this->get('/mr')
            ->assertOk()
            ->assertSee('मुख्यपृष्ठ', false);

        $this->get('/mr/services')
            ->assertOk()
            ->assertSee(__('site.services.banner_title', [], 'mr'), false);
    }

    public function test_language_links_switch_between_english_and_marathi(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee(url('/mr/about'), false);

        $this->get('/mr/about')
            ->assertOk()
            ->assertSee(url('/about'), false);
    }

    public function test_portfolio_lists_only_published_projects(): void
    {
        $published = PortfolioProject::factory()->create([
            'title' => 'Published Home',
            'slug' => 'published-home',
        ]);
        $draft = PortfolioProject::factory()->unpublished()->create([
            'title' => 'Draft Home',
            'slug' => 'draft-home',
        ]);

        $this->get('/projects')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title);
    }

    public function test_portfolio_filters_by_category(): void
    {
        $residential = PortfolioProject::factory()->create([
            'title' => 'Residential Build',
            'slug' => 'residential-build',
            'category' => 'residential',
        ]);
        $modern = PortfolioProject::factory()->create([
            'title' => 'Modern Build',
            'slug' => 'modern-build',
            'category' => 'modern-homes',
        ]);

        $this->get('/projects?category=modern-homes')
            ->assertOk()
            ->assertSee($modern->title)
            ->assertDontSee($residential->title);
    }

    public function test_portfolio_show_resolves_by_slug_and_hides_drafts(): void
    {
        $project = PortfolioProject::factory()->create([
            'title' => 'Courtyard Home',
            'slug' => 'courtyard-home',
        ]);
        $draft = PortfolioProject::factory()->unpublished()->create(['slug' => 'private-home']);

        $this->get('/projects/courtyard-home')
            ->assertOk()
            ->assertSee($project->title);

        $this->get('/projects/private-home')->assertNotFound();
        $this->get('/projects/missing-home')->assertNotFound();
    }

    public function test_sample_projects_are_labelled(): void
    {
        PortfolioProject::factory()->sample()->create(['slug' => 'sample-home']);

        $this->get('/projects')
            ->assertOk()
            ->assertSee(__('site.common.sample_notice'));

        $this->get('/projects/sample-home')
            ->assertOk()
            ->assertSee(__('site.common.sample_badge'));
    }

    public function test_contact_form_records_an_inquiry_and_notifies_the_owner(): void
    {
        Mail::fake();
        config(['site.contact.notify_email' => 'owner@example.com']);

        $this->post('/contact', $this->enquiry())
            ->assertRedirect(url('/contact').'#enquiry-form')
            ->assertSessionHas('inquiry_sent');

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Test Visitor',
            'phone' => '9021113887',
            'service' => 'residential-construction',
            'locale' => 'en',
        ]);

        Mail::assertSent(InquiryReceived::class, fn (InquiryReceived $mail): bool => $mail->hasTo('owner@example.com'));
    }

    public function test_contact_form_validates_input(): void
    {
        $this->post('/contact', [])
            ->assertSessionHasErrors(['name', 'phone', 'message']);

        $this->post('/contact', $this->enquiry(['phone' => '12345']))
            ->assertSessionHasErrors(['phone']);

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_honeypot_submissions_are_discarded(): void
    {
        $this->post('/contact', $this->enquiry(['company_website' => 'http://spam.example']))
            ->assertSessionHas('inquiry_sent');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_marathi_contact_form_records_the_locale(): void
    {
        Mail::fake();

        $this->post('/mr/contact', $this->enquiry())
            ->assertRedirect(url('/mr/contact').'#enquiry-form');

        $this->assertSame('mr', Inquiry::query()->firstOrFail()->locale);
    }

    public function test_sitemap_lists_both_languages(): void
    {
        PortfolioProject::factory()->create(['slug' => 'sitemap-home']);

        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = $response->getContent();

        $this->assertStringContainsString('<loc>'.url('/').'</loc>', $xml);
        $this->assertStringContainsString('<loc>'.url('/mr').'</loc>', $xml);
        $this->assertStringContainsString(url('/projects/sitemap-home'), $xml);
        $this->assertStringContainsString('hreflang="x-default"', $xml);
    }

    public function test_robots_file_keeps_internal_areas_out_of_search(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        foreach (['/admin', '/deploy', '/contractors'] as $path) {
            $this->assertStringContainsString('Disallow: '.$path, $robots);
        }
    }

    public function test_credits_page_lists_image_sources(): void
    {
        $this->get('/credits')
            ->assertOk()
            ->assertSee('Wikimedia Commons')
            ->assertSee('CC BY-SA');

        $this->get('/mr/credits')
            ->assertOk()
            ->assertSee(__('site.credits.title', [], 'mr'), false);
    }
}
