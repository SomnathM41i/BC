<?php

namespace Tests;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Contractor;
use App\Models\ContractorType;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function owner(): User
    {
        return User::factory()->create([
            'role' => UserRole::Owner,
            'is_active' => true,
        ]);
    }

    protected function contractorType(string $name = 'General'): ContractorType
    {
        return ContractorType::query()->firstOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );
    }

    protected function contractor(string $name = 'Contractor', ?string $phone = null): Contractor
    {
        return Contractor::factory()->create([
            'name' => $name,
            'phone' => $phone,
            'contractor_type_id' => $this->contractorType()->id,
            'is_active' => true,
        ]);
    }

    protected function projectCategory(string $name = 'Materials'): ExpenseCategory
    {
        return ExpenseCategory::query()->firstOrCreate(
            ['name' => $name, 'context' => ExpenseCategory::CONTEXT_PROJECT],
            ['is_active' => true]
        );
    }

    protected function ownerCategory(string $name = 'Owner Withdrawal'): ExpenseCategory
    {
        return ExpenseCategory::query()->firstOrCreate(
            ['name' => $name, 'context' => ExpenseCategory::CONTEXT_OWNER],
            ['is_active' => true]
        );
    }

    protected function ownerIncomeCategory(string $name = 'Salary'): ExpenseCategory
    {
        return ExpenseCategory::query()->firstOrCreate(
            ['name' => $name, 'context' => ExpenseCategory::CONTEXT_OWNER_INCOME],
            ['is_active' => true]
        );
    }

    protected function makeProject(Contractor $contractor, string $agreed = '1000000.00', string $name = 'Project A'): Project
    {
        return Project::create([
            'name' => $name,
            'location' => 'Pune',
            'agreed_amount' => $agreed,
            'contractor_id' => $contractor->id,
            'status' => ProjectStatus::Active,
        ]);
    }
}
