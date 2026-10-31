<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A payment can now cover every unpaid day, not just one week, so the day
     * counts can pass the 99.9 the old columns allowed.
     */
    public function up(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->decimal('present_days', 6, 1)->default(0)->change();
            $table->decimal('half_days', 6, 1)->default(0)->change();
            $table->decimal('total_payable_days', 6, 1)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->decimal('present_days', 3, 1)->default(0)->change();
            $table->decimal('half_days', 3, 1)->default(0)->change();
            $table->decimal('total_payable_days', 3, 1)->default(0)->change();
        });
    }
};
