<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Null means the amount is worked out from the normal day salary.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('half_day_amount', 14, 2)->nullable()->after('daily_salary');
            $table->decimal('long_day_amount', 14, 2)->nullable()->after('half_day_amount');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['half_day_amount', 'long_day_amount']);
        });
    }
};
