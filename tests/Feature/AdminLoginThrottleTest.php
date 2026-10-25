<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_is_throttled_after_five_failed_attempts(): void
    {
        $owner = $this->owner();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/admin/login', [
                'email' => $owner->email,
                'password' => 'not-the-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post('/admin/login', [
            'email' => $owner->email,
            'password' => 'not-the-password',
        ])->assertStatus(429);

        $this->assertGuest();
    }

    public function test_the_limit_is_applied_per_account_not_globally(): void
    {
        $owner = $this->owner();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/admin/login', [
                'email' => 'someone-else@bandgar.test',
                'password' => 'not-the-password',
            ])->assertSessionHasErrors('email');
        }

        // A different account from the same IP still signs in normally.
        $this->post('/admin/login', [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($owner);
    }
}
