<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('employee_type_id')->nullable()->after('phone_number')->constrained();
            // Pay now comes from pay rates; the old value is kept for reference.
            $table->decimal('daily_salary', 14, 2)->nullable()->change();
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->string('work_level', 20)->nullable()->after('status');
            $table->string('hours_label', 20)->nullable()->after('work_level');
            $table->decimal('amount', 14, 2)->default(0)->after('payable_days');
            $table->decimal('adjustment_amount', 14, 2)->default(0)->after('amount');
            $table->string('adjustment_note')->nullable()->after('adjustment_amount');
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
            $table->dropColumn(['work_level', 'hours_label', 'amount', 'adjustment_amount', 'adjustment_note']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_type_id');
        });
    }
};
