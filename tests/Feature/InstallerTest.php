<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    public function test_installer_is_disabled_when_no_token_is_configured(): void
    {
        config(['deploy.token' => '']);

        $this->get('/deploy')->assertNotFound();
    }

    public function test_installer_page_loads_when_a_token_is_configured(): void
    {
        config(['deploy.token' => 'secret-token']);

        $this->get('/deploy')
            ->assertOk()
            ->assertSee('Deployment token')
            ->assertSee('Install (migrate + seed)');
    }

    public function test_running_an_action_with_a_wrong_token_is_rejected(): void
    {
        config(['deploy.token' => 'secret-token']);

        $this->post('/deploy/run', ['token' => 'nope', 'action' => 'migrate'])
            ->assertStatus(403)
            ->assertSee('Authentication failed');
    }

    public function test_seeder_can_be_run_through_the_installer(): void
    {
        config(['deploy.token' => 'secret-token']);

        $this->post('/deploy/run', ['token' => 'secret-token', 'action' => 'seed'])
            ->assertOk()
            ->assertSee('Ran: Run seeders');

        $this->assertDatabaseHas('users', ['email' => 'owner@bandgar.test']);
    }

    public function test_owner_account_can_be_created_through_the_installer(): void
    {
        config(['deploy.token' => 'secret-token']);

        $this->post('/deploy/run', [
            'token' => 'secret-token',
            'action' => 'owner',
            'owner_name' => 'Somnath',
            'owner_email' => 'somnath@example.com',
            'owner_password' => 'supersecret',
        ])->assertOk()->assertSee('Owner account ready');

        $user = User::query()->where('email', 'somnath@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Owner, $user->role);
        $this->assertTrue($user->is_active);
    }
}
