<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        DB::table('contractors')->orderBy('id')->get()->each(function (object $contractor): void {
            $base = Str::slug((string) $contractor->name) ?: 'contractor-'.$contractor->id;
            $slug = $base;
            $suffix = 2;

            while (DB::table('contractors')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            DB::table('contractors')->where('id', $contractor->id)->update(['slug' => $slug]);
        });

        Schema::table('contractors', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
