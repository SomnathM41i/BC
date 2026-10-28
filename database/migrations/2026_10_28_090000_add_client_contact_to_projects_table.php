<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client contact shown through the public "Track Your Payments" portal.
     *
     * The normalized number is what the portal searches by; the original value
     * is kept only for display in the admin so the owner can see what they
     * entered.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('location');
            $table->string('client_mobile')->nullable()->after('client_name');
            $table->string('client_mobile_normalized', 20)->nullable()->after('client_mobile');

            $table->index('client_mobile_normalized');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['client_mobile_normalized']);
            $table->dropColumn(['client_name', 'client_mobile', 'client_mobile_normalized']);
        });
    }
};
