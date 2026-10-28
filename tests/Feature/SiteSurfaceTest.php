<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_renders_the_business_website(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bandgar', false)
            ->assertSee('brand-logo-header.png', false)
            ->assertSee('modern-duplex-residence/cover.jpg', false)
            ->assertSee(__('site.home.hero_title', [], 'en'), false);

        $this->get('/mr')
            ->assertOk()
            ->assertSee('Bandgar Construction', false)
            ->assertSee('modern-duplex-residence/cover.jpg', false)
            ->assertSee(__('site.home.hero_title', [], 'mr'), false);
    }

    public function test_legacy_internal_urls_redirect_under_admin(): void
    {
        $this->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->get('/owner')->assertRedirect('/admin/owner');
        $this->get('/login')->assertRedirect('/admin/login');
    }

    public function test_projects_url_now_belongs_to_the_public_portfolio(): void
    {
        $this->get('/projects')
            ->assertOk()
            ->assertSee(__('site.meta.portfolio.title'));
    }
}
