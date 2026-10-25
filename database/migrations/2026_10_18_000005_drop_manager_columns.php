<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('projects', 'manager_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropConstrainedForeignId('manager_id');
            });
        }

        if (Schema::hasColumn('wallet_transactions', 'manager_id')) {
            // On MySQL/MariaDB the foreign key is backed by the composite
            // (manager_id, type) index, so the constraint must be dropped before
            // the index it depends on.
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
            });

            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->dropIndex(['manager_id', 'type']);
            });

            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->dropColumn('manager_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('projects', 'manager_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('manager_id')->nullable()->after('contractor_id')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('wallet_transactions', 'manager_id')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->foreignId('manager_id')->nullable()->after('contractor_id')->constrained('users')->nullOnDelete();
                $table->index(['manager_id', 'type']);
            });
        }
    }
};
