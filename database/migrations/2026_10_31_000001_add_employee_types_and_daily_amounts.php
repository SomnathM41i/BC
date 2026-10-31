<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Earlier pay-rate migrations were pushed before this replaced them, so a
     * database may or may not have run them. Each step checks what exists, so
     * both end up with the same schema and every day keeps what it pays.
     */
    public function up(): void
    {
        if (! Schema::hasTable('employee_types')) {
            Schema::create('employee_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->timestamps();
            });

            foreach (['Labour', 'Mistri', 'Other'] as $index => $name) {
                DB::table('employee_types')->insert([
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! Schema::hasColumn('employees', 'employee_type_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->foreignId('employee_type_id')->nullable()->after('phone_number')->constrained();
            });
        }

        if (! Schema::hasColumn('employee_attendances', 'amount')) {
            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->decimal('amount', 14, 2)->default(0)->after('payable_days');
            });
        }

        if (Schema::hasColumn('employee_attendances', 'adjustment_amount')) {
            DB::table('employee_attendances')->update(['amount' => DB::raw('amount + adjustment_amount')]);

            Schema::table('employee_attendances', function (Blueprint $table) {
                $table->dropColumn(['work_level', 'hours_label', 'adjustment_amount', 'adjustment_note']);
            });
        }

        if (Schema::hasColumn('employee_salary_payments', 'rate_breakdown')) {
            Schema::table('employee_salary_payments', function (Blueprint $table) {
                $table->dropColumn('rate_breakdown');
            });
        }

        Schema::dropIfExists('employee_pay_rate_overrides');
        Schema::dropIfExists('employee_pay_rates');

        if (Schema::hasColumn('employees', 'daily_salary')) {
            $otherTypeId = DB::table('employee_types')->where('name', 'Other')->value('id');
            DB::table('employees')->whereNull('employee_type_id')->update(['employee_type_id' => $otherTypeId]);

            // Copy the old salary onto days not yet given an amount, then the
            // daily salary is no longer needed.
            $salaries = DB::table('employees')->pluck('daily_salary', 'id');

            DB::table('employee_attendances')
                ->where('status', '!=', 'absent')
                ->where('amount', 0)
                ->orderBy('id')
                ->get()
                ->each(function ($attendance) use ($salaries) {
                    DB::table('employee_attendances')->where('id', $attendance->id)->update([
                        'amount' => bcmul((string) ($salaries[$attendance->employee_id] ?? '0'), (string) $attendance->payable_days, 2),
                    ]);
                });

            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('daily_salary');
            });
        }

        DB::table('employee_attendances')->where('status', 'half_day')->update(['status' => 'present', 'payable_days' => '1.0']);

        DB::table('migrations')->whereIn('migration', [
            '2026_10_31_000001_create_employee_types_and_pay_rates_tables',
            '2026_10_31_000002_add_work_pay_columns_to_employee_tables',
            '2026_10_31_000003_backfill_employee_pay_data',
            '2026_10_31_000004_simplify_employee_pay_to_daily_amounts',
        ])->delete();
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('daily_salary', 14, 2)->nullable()->after('employee_type_id');
            $table->dropConstrainedForeignId('employee_type_id');
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::dropIfExists('employee_types');
    }
};
