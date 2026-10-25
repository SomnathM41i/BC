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
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/projects')->assertRedirect('/login');
        $this->get('/contractors')->assertRedirect('/login');
    }

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $owner = $this->owner();

        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($owner);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Owner,
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_owner_can_access_the_owner_personal_wallet(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/owner')->assertOk();
    }

    public function test_owner_can_view_the_dashboard(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/dashboard')->assertOk();
    }

    public function test_owner_can_view_a_project(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor();
        $project = $this->makeProject($contractor, '100000.00', 'My Project');

        $this->actingAs($owner)
            ->get("/projects/{$project->id}")
            ->assertOk()
            ->assertSee('My Project');
    }
}
