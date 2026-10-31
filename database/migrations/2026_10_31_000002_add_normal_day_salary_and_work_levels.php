<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The old daily salary is gone, so existing employees start at 0 and cannot
     * be marked until the owner sets it. Marked days keep their amount.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('daily_salary', 14, 2)->default(0)->after('employee_type_id');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->string('work_level', 20)->nullable()->after('status');
        });

        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->json('rate_breakdown')->nullable()->after('total_payable_days');
        });
    }

    public function down(): void
    {
        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->dropColumn('rate_breakdown');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn('work_level');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('daily_salary');
        });
    }
};
