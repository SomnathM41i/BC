<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\WalletType;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'owner@bandgar.test'],
            [
                'name' => 'Owner / Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'is_active' => true,
            ]
        );

        $projectCategories = ['Materials', 'Labour', 'Transport', 'Contractor', 'Equipment', 'Miscellaneous'];
        foreach ($projectCategories as $name) {
            ExpenseCategory::query()->firstOrCreate(
                ['name' => $name, 'context' => ExpenseCategory::CONTEXT_PROJECT],
                ['is_active' => true]
            );
        }

        $ownerCategories = ['Owner Withdrawal', 'Remuneration', 'Reimbursement', 'Personal Expense'];
        foreach ($ownerCategories as $name) {
            ExpenseCategory::query()->firstOrCreate(
                ['name' => $name, 'context' => ExpenseCategory::CONTEXT_OWNER],
                ['is_active' => true]
            );
        }

        $ownerIncomeCategories = ['Salary', 'Business Income', 'Rent Received', 'Interest', 'Other Personal Income'];
        foreach ($ownerIncomeCategories as $name) {
            ExpenseCategory::query()->firstOrCreate(
                ['name' => $name, 'context' => ExpenseCategory::CONTEXT_OWNER_INCOME],
                ['is_active' => true]
            );
        }

        Wallet::query()->firstOrCreate(
            ['type' => WalletType::Business->value, 'project_id' => null, 'contractor_id' => null],
            ['balance' => '0.00']
        );

        Wallet::query()->firstOrCreate(
            ['type' => WalletType::Owner->value, 'project_id' => null, 'contractor_id' => null],
            ['balance' => '0.00']
        );

        // Public portfolio content lives in a seeder because there is no admin
        // screen for it yet; running this keeps a fresh deploy populated.
        $this->call(RealPortfolioSeeder::class);
    }
}
