<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->get('/admin/projects')->assertRedirect('/admin/login');
        $this->get('/admin/contractors')->assertRedirect('/admin/login');
    }

    public function test_login_screen_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Sign in');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $owner = $this->owner();

        $this->post('/admin/login', [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($owner);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Owner,
            'is_active' => false,
        ]);

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_owner_can_access_the_owner_personal_wallet(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/admin/owner')->assertOk();
    }

    public function test_owner_can_view_the_dashboard(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/admin/dashboard')->assertOk();
    }

    public function test_owner_can_view_a_project(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '100000.00', 'My Project');

        $this->actingAs($owner)
            ->get("/admin/projects/{$project->id}")
            ->assertOk()
            ->assertSee('My Project');
    }
}
