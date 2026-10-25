<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('contractor_id')->nullable()->after('agreed_amount')->constrained('contractors')->nullOnDelete();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('contractor_id')->nullable()->after('manager_id')->constrained('contractors')->nullOnDelete();
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->foreignId('contractor_id')->nullable()->after('user_id')->constrained('contractors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contractor_id');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contractor_id');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contractor_id');
        });
    }
};
