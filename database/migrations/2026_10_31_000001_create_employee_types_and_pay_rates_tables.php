<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

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

        // Types and levels are fixed, so they ship with the schema. Only the two
        // Labour amounts are confirmed; ₹0 means "not set yet" and attendance
        // cannot be marked against it.
        $now = now();
        $rates = [
            'Labour' => ['half_day' => ['4', '0.00'], 'normal_day' => ['6-8', '600.00'], 'long_day' => ['12', '900.00']],
            'Mistri' => ['half_day' => ['4', '0.00'], 'normal_day' => ['8', '0.00'], 'long_day' => ['12', '0.00']],
            'Other' => ['half_day' => ['4', '0.00'], 'normal_day' => ['8', '0.00'], 'long_day' => ['12', '0.00']],
        ];

        $order = 0;
        foreach ($rates as $name => $levels) {
            $typeId = DB::table('employee_types')->insertGetId([
                'name' => $name,
                'sort_order' => ++$order,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($levels as $level => [$hours, $amount]) {
                DB::table('employee_pay_rates')->insert([
                    'employee_type_id' => $typeId,
                    'work_level' => $level,
                    'hours_label' => $hours,
                    'amount' => $amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_pay_rate_overrides');
        Schema::dropIfExists('employee_pay_rates');
        Schema::dropIfExists('employee_types');
    }
};
