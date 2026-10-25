<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $otherTypeId = DB::table('contractor_types')->where('name', 'Other')->value('id');
        $now = now();

        // 1. Convert each manager user into a contractor record.
        $managerUsers = DB::table('users')
            ->where('role', 'manager')
            ->get(['id', 'name']);

        $userIdToContractorId = [];

        foreach ($managerUsers as $user) {
            $contractorId = DB::table('contractors')->insertGetId([
                'name' => $user->name,
                'phone' => null,
                'contractor_type_id' => $otherTypeId,
                'notes' => 'Migrated from manager account #'.$user->id,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $userIdToContractorId[$user->id] = $contractorId;
        }

        // 2. Remap projects.manager_id -> projects.contractor_id.
        foreach ($userIdToContractorId as $userId => $contractorId) {
            DB::table('projects')->where('manager_id', $userId)->update(['contractor_id' => $contractorId]);
        }

        // 3. Remap wallet_transactions.manager_id -> wallet_transactions.contractor_id
        //    and rewrite transaction type strings.
        foreach ($userIdToContractorId as $userId => $contractorId) {
            DB::table('wallet_transactions')->where('manager_id', $userId)->update(['contractor_id' => $contractorId]);
        }

        DB::table('wallet_transactions')->where('type', 'manager_transfer')->update(['type' => 'contractor_transfer']);
        DB::table('wallet_transactions')->where('type', 'manager_return')->update(['type' => 'contractor_return']);

        // 4. Create a contractor wallet for each contractor with the net outstanding
        //    balance (transfers minus returns) so historical truth is preserved.
        foreach ($userIdToContractorId as $userId => $contractorId) {
            $transfers = DB::table('wallet_transactions')
                ->where('contractor_id', $contractorId)
                ->where('type', 'contractor_transfer')
                ->sum('amount');

            $returns = DB::table('wallet_transactions')
                ->where('contractor_id', $contractorId)
                ->where('type', 'contractor_return')
                ->sum('amount');

            $balance = number_format($transfers - $returns, 2, '.', '');

            DB::table('wallets')->insert([
                'type' => 'contractor',
                'project_id' => null,
                'user_id' => null,
                'contractor_id' => $contractorId,
                'balance' => $balance,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // 5. Delete all old manager user accounts (per Emperor's instruction).
        DB::table('users')->where('role', 'manager')->delete();
    }

    public function down(): void
    {
        // Irreversible data migration: contractor rows and remapped FKs cannot be
        // reconstructed as manager users without the pre-migration backup.
        // Restore from mysql_recovery_dumps/bandgar_wallet_pre_contractor_*.sql instead.
    }
};
