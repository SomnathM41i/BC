<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_page_renders_the_placeholder_site(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bandgar Construction')
            ->assertSee('on the way', false);
    }

    public function test_legacy_internal_urls_redirect_under_admin(): void
    {
        $this->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->get('/projects')->assertRedirect('/admin/projects');
        $this->get('/projects/12/receipts')->assertRedirect('/admin/projects/12/receipts');
        $this->get('/owner')->assertRedirect('/admin/owner');
        $this->get('/login')->assertRedirect('/admin/login');
    }
}
