<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The enquiry form now asks for the project location and an optional
     * budget. Both are free text on purpose: visitors write budgets in many
     * shapes ("25 lakh", "₹25–30 लाख"), and nothing is calculated from them.
     */
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('location', 120)->nullable()->after('phone');
            $table->string('budget', 60)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['location', 'budget']);
        });
    }
};
