<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ManagerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_the_managers_page(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->get('/managers')
            ->assertOk()
            ->assertSee('Add a manager');
    }

    public function test_owner_can_create_a_manager(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/managers', [
                'name' => 'Suresh Patil',
                'email' => 'suresh@bandgar.test',
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
            ])
            ->assertRedirect('/managers');

        $this->assertDatabaseHas('users', [
            'email' => 'suresh@bandgar.test',
            'name' => 'Suresh Patil',
            'role' => UserRole::Manager->value,
            'is_active' => true,
        ]);
    }

    public function test_created_manager_password_is_shown_once_after_creation(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/managers', [
                'name' => 'Once Only',
                'email' => 'once@bandgar.test',
                'password' => 'shown-once-pass',
                'password_confirmation' => 'shown-once-pass',
            ])
            ->assertRedirect('/managers');

        // Shown on the next page load...
        $this->actingAs($owner)
            ->get('/managers')
            ->assertOk()
            ->assertSee('Save these credentials now')
            ->assertSee('shown-once-pass');

        // ...and not again afterwards.
        $this->actingAs($owner)
            ->get('/managers')
            ->assertOk()
            ->assertDontSee('shown-once-pass');
    }

    public function test_manager_creation_requires_a_unique_email(): void
    {
        $owner = $this->owner();
        $this->manager('Existing')->update(['email' => 'taken@bandgar.test']);

        $this->actingAs($owner)
            ->post('/managers', [
                'name' => 'Duplicate',
                'email' => 'taken@bandgar.test',
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_manager_creation_requires_matching_password_confirmation(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/managers', [
                'name' => 'No Match',
                'email' => 'nomatch@bandgar.test',
                'password' => 'secret-pass',
                'password_confirmation' => 'different-pass',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_project_manager_cannot_access_the_managers_page(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->get('/managers')->assertForbidden();
        $this->actingAs($manager)->post('/managers', [
            'name' => 'X',
            'email' => 'x@bandgar.test',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertForbidden();
    }

    public function test_created_manager_appears_in_the_project_manager_dropdown(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post('/managers', [
            'name' => 'New Manager',
            'email' => 'new@bandgar.test',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);

        $this->actingAs($owner)
            ->get('/projects/create')
            ->assertOk()
            ->assertSee('New Manager');
    }

    public function test_managers_are_created_as_manager_role_with_hashed_password(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post('/managers', [
            'name' => 'Hashed',
            'email' => 'hashed@bandgar.test',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);

        $user = User::query()->where('email', 'hashed@bandgar.test')->firstOrFail();

        $this->assertSame(UserRole::Manager, $user->role);
        $this->assertNotSame('secret-pass', $user->password);
        $this->assertTrue(Hash::check('secret-pass', $user->password));
    }
}
