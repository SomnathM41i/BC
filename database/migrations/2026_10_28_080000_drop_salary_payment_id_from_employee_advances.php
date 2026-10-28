<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repayments live on the salary payment itself (employee_salary_payments
     * .advance_repayment). Linking an advance row to one salary payment could
     * not represent a partial repayment without overstating the amount repaid,
     * so the extra column is no longer used.
     */
    public function up(): void
    {
        if (Schema::hasColumn('employee_advances', 'salary_payment_id')) {
            Schema::table('employee_advances', function (Blueprint $table) {
                $table->dropForeign(['salary_payment_id']);
                $table->dropColumn('salary_payment_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->foreignId('salary_payment_id')->nullable()->after('notes')
                ->constrained('employee_salary_payments')->nullOnDelete();
        });
    }
};
