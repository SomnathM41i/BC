<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pay is now one amount entered per present day. What each day pays is kept
     * by folding adjustments into its amount before the columns are dropped.
     */
    public function up(): void
    {
        DB::table('employee_attendances')->update(['amount' => DB::raw('amount + adjustment_amount')]);
        DB::table('employee_attendances')->where('status', 'half_day')->update(['status' => 'present', 'payable_days' => '1.0']);

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn(['work_level', 'hours_label', 'adjustment_amount', 'adjustment_note']);
        });

        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->dropColumn('rate_breakdown');
        });

        Schema::dropIfExists('employee_pay_rate_overrides');
        Schema::dropIfExists('employee_pay_rates');
    }

    public function down(): void
    {
        Schema::create('employee_pay_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_type_id')->constrained()->cascadeOnDelete();
            $table->string('work_level', 20);
            $table->string('hours_label', 20)->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_type_id', 'work_level']);
        });

        Schema::create('employee_pay_rate_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('work_level', 20);
            $table->decimal('amount', 14, 2);
            $table->timestamps();

            $table->unique(['employee_id', 'work_level']);
        });

        Schema::table('employee_salary_payments', function (Blueprint $table) {
            $table->json('rate_breakdown')->nullable()->after('total_payable_days');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->string('work_level', 20)->nullable()->after('status');
            $table->string('hours_label', 20)->nullable()->after('work_level');
            $table->decimal('adjustment_amount', 14, 2)->default(0)->after('amount');
            $table->string('adjustment_note')->nullable()->after('adjustment_amount');
        });
    }
};
