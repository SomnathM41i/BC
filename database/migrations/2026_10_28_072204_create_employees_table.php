<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('full_name');
            $table->string('phone_number');
            $table->decimal('daily_salary', 14, 2);
            $table->string('weekly_salary_payment_day');
            $table->date('joining_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['status', 'full_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
