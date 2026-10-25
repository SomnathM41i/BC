<?php

namespace Tests\Feature;

use App\Models\Contractor;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_the_contractors_page(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->get('/admin/contractors')
            ->assertOk()
            ->assertSee('Contractor Directory')
            ->assertSee('Total Given');
    }

    public function test_public_contractor_link_shows_payment_breakdown_without_login(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor('Ramesh Jadhav', '+91 9000000000');
        $project = $this->makeProject($contractor, '1000000.00', 'Tower A');
        $category = $this->projectCategory('Labour');

        $this->actingAs($owner)->post("/admin/projects/{$project->id}/receipts", [
            'amount' => '50000',
            'transaction_date' => '2026-10-01',
        ]);

        $this->actingAs($owner)->post("/admin/projects/{$project->id}/transfers", [
            'amount' => '12000',
            'category_id' => $category->id,
            'contractor_id' => $contractor->id,
            'transaction_date' => '2026-10-02',
        ]);

        $this->actingAs($owner)->post("/admin/projects/{$project->id}/transfers", [
            'amount' => '8000',
            'category_id' => $category->id,
            'contractor_id' => $contractor->id,
            'transaction_date' => '2026-10-05',
        ]);

        // The public link carries a random token, not the contractor's name.
        $this->assertSame('ramesh-jadhav', $contractor->slug);

        $token = $contractor->statementToken();

        $this->assertNotSame($contractor->slug, $token);
        $this->assertSame(48, strlen($token));

        $this->get("/statement/{$token}")
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet')
            ->assertSee('Ramesh Jadhav')
            ->assertSee('Tower A')
            ->assertSee('Payments by Project')
            ->assertSee('₹20,000.00')
            ->assertDontSee('9000000000')
            ->assertDontSee($contractor->slug);

        // The old guessable URL no longer exposes anything.
        $this->get("/contractors/{$contractor->slug}")
            ->assertStatus(410)
            ->assertDontSee('Ramesh Jadhav');

        // An unknown token is simply not found.
        $this->get('/statement/this-token-does-not-exist')
            ->assertNotFound();
    }

    public function test_owner_can_create_a_contractor_and_a_wallet_is_created(): void
    {
        $owner = $this->owner();
        $type = $this->contractorType('Masonry');

        $this->actingAs($owner)
            ->post('/admin/contractors', [
                'name' => 'Suresh Pawar',
                'phone' => '+91 9876543210',
                'contractor_type_id' => $type->id,
                'notes' => 'Handles brickwork',
            ])
            ->assertRedirect('/admin/contractors');

        $contractor = Contractor::query()->where('name', 'Suresh Pawar')->firstOrFail();

        $this->assertSame('+91 9876543210', $contractor->phone);
        $this->assertSame($type->id, $contractor->contractor_type_id);
        $this->assertTrue($contractor->is_active);

        // A wallet is created automatically and starts at zero.
        $wallet = Wallet::query()->where('contractor_id', $contractor->id)->first();
        $this->assertNotNull($wallet);
        $this->assertSame('0.00', $wallet->balance);
    }

    public function test_contractor_name_is_required(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/admin/contractors', [
                'name' => '',
                'contractor_type_id' => $this->contractorType()->id,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_contractor_type_must_exist(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post('/admin/contractors', [
                'name' => 'Suresh',
                'contractor_type_id' => 9999,
            ])
            ->assertSessionHasErrors('contractor_type_id');
    }

    public function test_duplicate_contractor_name_and_phone_is_rejected(): void
    {
        $owner = $this->owner();
        $type = $this->contractorType('General');

        $this->actingAs($owner)->post('/admin/contractors', [
            'name' => 'Suresh Pawar',
            'phone' => '+91 9876543210',
            'contractor_type_id' => $type->id,
        ])->assertRedirect('/admin/contractors');

        $this->actingAs($owner)->post('/admin/contractors', [
            'name' => 'Suresh Pawar',
            'phone' => '+91 9876543210',
            'contractor_type_id' => $type->id,
        ])->assertSessionHasErrors('name');

        $this->assertSame(1, Contractor::query()->where('name', 'Suresh Pawar')->count());
    }

    public function test_recipient_must_be_an_active_contractor(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor();
        $contractor->update(['is_active' => false]);

        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        $this->actingAs($owner)->post("/admin/projects/{$project->id}/receipts", [
            'amount' => '5000',
            'transaction_date' => '2026-10-01',
        ]);

        $this->actingAs($owner)
            ->post("/admin/projects/{$project->id}/transfers", [
                'amount' => '1000',
                'category_id' => $category->id,
                'contractor_id' => $contractor->id,
                'transaction_date' => '2026-10-02',
            ])
            ->assertSessionHasErrors('contractor_id');

        $this->assertSame('0.00', app(WalletService::class)->contractorBalance($contractor));
    }

    public function test_creating_a_contractor_creates_no_login_account(): void
    {
        $owner = $this->owner();
        $type = $this->contractorType('General');

        $this->actingAs($owner)->post('/admin/contractors', [
            'name' => 'No Login Required',
            'phone' => null,
            'contractor_type_id' => $type->id,
            'email' => 'should-not-be-used@example.com',
            'password' => 'secret-password',
        ])->assertRedirect('/admin/contractors');

        Contractor::query()->where('name', 'No Login Required')->firstOrFail();

        // Only the owner exists; no user account is created for the contractor.
        $this->assertSame(1, \App\Models\User::query()->count());
        $this->assertDatabaseMissing('users', ['email' => 'should-not-be-used@example.com']);
    }

    public function test_new_contractor_can_be_created_inline_during_a_transfer(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor('Existing Contractor');
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        $this->actingAs($owner)->post("/admin/projects/{$project->id}/receipts", [
            'amount' => '5000',
            'transaction_date' => '2026-10-01',
        ]);

        $this->actingAs($owner)
            ->post("/admin/projects/{$project->id}/transfers", [
                'amount' => '1000',
                'category_id' => $category->id,
                'contractor_id' => '__new__',
                'new_contractor_name' => 'Fresh Subcontractor',
                'new_contractor_phone' => '+91 9000000000',
                'transaction_date' => '2026-10-02',
            ])
            ->assertRedirect("/admin/projects/{$project->id}");

        $new = Contractor::query()->where('name', 'Fresh Subcontractor')->firstOrFail();

        $this->assertSame('+91 9000000000', $new->phone);
        $this->assertNull($new->contractor_type_id);
        $this->assertTrue($new->is_active);

        $wallet = Wallet::query()->where('contractor_id', $new->id)->firstOrFail();
        $this->assertSame('1000.00', $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'contractor_id' => $new->id,
            'type' => 'contractor_transfer',
            'amount' => '1000.00',
        ]);
    }

    public function test_inline_new_contractor_requires_name_and_phone(): void
    {
        $owner = $this->owner();
        $contractor = $this->contractor('Existing Contractor');
        $project = $this->makeProject($contractor, '1000000.00');
        $category = $this->projectCategory('Labour');

        $this->actingAs($owner)->post("/admin/projects/{$project->id}/receipts", [
            'amount' => '5000',
            'transaction_date' => '2026-10-01',
        ]);

        $this->actingAs($owner)
            ->post("/admin/projects/{$project->id}/transfers", [
                'amount' => '1000',
                'category_id' => $category->id,
                'contractor_id' => '__new__',
                'transaction_date' => '2026-10-02',
            ])
            ->assertSessionHasErrors(['new_contractor_name', 'new_contractor_phone']);
    }
}
