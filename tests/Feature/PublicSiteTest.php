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

    public function test_marathi_is_the_default_language(): void
    {
        foreach (['/', '/about', '/services', '/projects', '/contact'] as $url) {
            $this->get($url)->assertOk()->assertSee('lang="mr-IN"', false);
        }

        $this->get('/')->assertSee('modern-duplex-residence/cover.jpg', false);
    }

    public function test_english_pages_render_under_the_en_prefix(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('lang="en-IN"', false)
            ->assertSee('modern-duplex-residence/cover.jpg', false);

        $this->get('/en/services')
            ->assertOk()
            ->assertSee(__('site.services.banner_title', [], 'en'), false);
    }

    public function test_language_links_switch_between_english_and_marathi(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee(url('/en/about'), false)
            ->assertSee(url('/about'), false)
            ->assertSee('hreflang="mr-IN"', false)
            ->assertSee('hreflang="en-IN"', false);

        $this->get('/en/about')
            ->assertOk()
            ->assertSee(url('/about'), false)
            ->assertSee(url('/en/about'), false);
    }

    public function test_public_contact_details_match_the_official_address_and_signboard_numbers(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('करगणी, शेटफळे रोड', false)
            ->assertSee('ता. आटपाडी, जि. सांगली', false)
            ->assertSee('इंजि. भरत बंडगर', false)
            ->assertSee('href="tel:+919021113887"', false)
            ->assertSee('https://www.google.com/maps/embed?pb=', false)
            ->assertSee('https://www.google.com/maps/search/?api=1', false)
            ->assertDontSee('बंदगर', false)
            ->assertDontSee('संजोराव', false)
            ->assertDontSee('शेतफुळे', false)
            ->assertDontSee('गावठाण', false);

        $this->get('/en/contact')
            ->assertOk()
            ->assertSee('Kargani, Shetfale Road', false)
            ->assertSee('Taluka Atpadi, Sangli District', false)
            ->assertSee('Er. Bharat Bandgar', false)
            ->assertDontSee('Gav Number 2', false)
            ->assertDontSee('Sanjorao', false)
            ->assertDontSee('Shetphule', false)
            ->assertDontSee('Gaothan No. 2', false);
    }

    public function test_language_switch_preserves_project_context_and_filters(): void
    {
        $project = PortfolioProject::factory()->create([
            'title' => 'Courtyard Home',
            'title_mr' => 'आंगण असलेले घर',
            'slug' => 'courtyard-home',
            'category' => 'modern-homes',
        ]);

        $this->get('/projects?category=modern-homes')
            ->assertOk()
            ->assertSee(url('/en/projects?category=modern-homes'), false);

        $this->get('/projects/'.$project->slug)
            ->assertOk()
            ->assertSee(url('/en/projects/'.$project->slug), false);
    }

    public function test_old_marathi_urls_redirect_to_the_canonical_root(): void
    {
        $this->get('/mr/services')->assertRedirect('/services');
        $this->get('/mr/projects?category=modern-homes')->assertRedirect('/projects?category=modern-homes');
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

    public function test_portfolio_carries_no_template_placeholder_copy(): void
    {
        PortfolioProject::factory()->create(['slug' => 'portfolio-check']);

        foreach (['/projects', '/projects/portfolio-check'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('Illustrative sample', false)
                ->assertDontSee('being prepared', false)
                ->assertDontSee('Wikimedia Commons', false);
        }
    }

    public function test_contact_form_records_an_inquiry_and_notifies_the_owner(): void
    {
        Mail::fake();
        config(['site.contact.notify_email' => 'owner@example.com']);

        $this->post('/en/contact', $this->enquiry())
            ->assertRedirect(url('/en/contact').'#enquiry-form')
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
        $this->post('/en/contact', [])
            ->assertSessionHasErrors(['name', 'phone', 'message']);

        $this->post('/en/contact', $this->enquiry(['phone' => '12345']))
            ->assertSessionHasErrors(['phone']);

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_contact_form_keeps_the_project_location_and_budget(): void
    {
        Mail::fake();

        $this->post('/en/contact', $this->enquiry([
            'location' => 'Kargani, Atpadi',
            'budget' => '₹25–30 lakh',
        ]))->assertSessionHas('inquiry_sent');

        $inquiry = Inquiry::query()->firstOrFail();

        $this->assertSame('Kargani, Atpadi', $inquiry->location);
        $this->assertSame('₹25–30 lakh', $inquiry->budget);
    }

    public function test_contact_page_preselects_the_service_from_the_query_string(): void
    {
        $this->get('/en/contact?service=3d-design')
            ->assertOk()
            ->assertSee('value="3d-design" selected', false);

        // An unknown slug falls back to no selection rather than breaking.
        $this->get('/en/contact?service=not-a-service')
            ->assertOk()
            ->assertDontSee('value="not-a-service" selected', false);
    }

    public function test_testimonials_stay_hidden_until_real_reviews_are_added(): void
    {
        config(['site.testimonials' => []]);

        $this->get('/en')->assertOk()->assertDontSee(__('site.home.testimonials_title', [], 'en'), false);

        config(['site.testimonials' => [
            ['quote' => 'Clear communication throughout.', 'name' => 'A Client', 'context' => 'Home in Atpadi'],
        ]]);

        $this->get('/en')->assertOk()->assertSee('Clear communication throughout.', false);
    }

    public function test_honeypot_submissions_are_discarded(): void
    {
        $this->post('/en/contact', $this->enquiry(['company_website' => 'http://spam.example']))
            ->assertSessionHas('inquiry_sent');

        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_marathi_contact_form_records_the_locale(): void
    {
        Mail::fake();

        $this->post('/contact', $this->enquiry())
            ->assertRedirect(url('/contact').'#enquiry-form');

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
        $this->assertStringContainsString('<loc>'.url('/en').'</loc>', $xml);
        $this->assertStringContainsString(url('/projects/sitemap-home'), $xml);
        $this->assertStringContainsString(url('/en/projects/sitemap-home'), $xml);
        $this->assertStringContainsString('hreflang="x-default"', $xml);
    }

    public function test_robots_file_keeps_internal_areas_out_of_search(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        foreach (['/admin', '/deploy', '/contractors'] as $path) {
            $this->assertStringContainsString('Disallow: '.$path, $robots);
        }
    }

    public function test_credits_page_has_been_removed(): void
    {
        $this->get('/credits')->assertNotFound();
        $this->get('/en/credits')->assertNotFound();

        $this->get('/en')
            ->assertOk()
            ->assertDontSee(url('/en/credits'), false)
            ->assertDontSee(url('/credits'), false);
    }
}
