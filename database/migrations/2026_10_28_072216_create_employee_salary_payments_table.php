<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('daily_salary', 14, 2);
            $table->decimal('gross_salary', 14, 2);
            $table->decimal('present_days', 3, 1)->default(0);
            $table->decimal('half_days', 3, 1)->default(0);
            $table->decimal('total_payable_days', 3, 1)->default(0);
            $table->decimal('outstanding_advance_before', 14, 2)->default('0.00');
            $table->decimal('advance_repayment', 14, 2)->default('0.00');
            $table->decimal('net_salary_payable', 14, 2);
            $table->decimal('amount_paid', 14, 2);
            $table->decimal('remaining_salary_due', 14, 2)->default('0.00');
            $table->decimal('remaining_advance_after', 14, 2)->default('0.00');
            $table->enum('status', ['paid', 'partially_paid']);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period_start', 'period_end'], 'emp_sal_pay_unique');
            $table->index(['payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salary_payments');
    }
};
