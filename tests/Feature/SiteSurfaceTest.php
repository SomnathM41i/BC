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
            ->assertSee('बंदगर', false)
            ->assertSee('तुमच्या स्वप्नातील घराला देऊया सुंदर आकार!', false);

        $this->get('/en')
            ->assertOk()
            ->assertSee('Bandgar Construction', false)
            ->assertSee('A Better Shape for Your Dream Home', false);
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
