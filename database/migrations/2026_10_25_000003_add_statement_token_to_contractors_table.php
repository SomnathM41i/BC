<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Public contractor statements move from guessable slugs to unguessable
     * tokens. Existing contractors are backfilled here so no link is lost
     * silently; the old slug URL now answers 410 Gone.
     */
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('statement_token', 64)->nullable()->after('slug');
        });

        DB::table('contractors')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($contractors): void {
                foreach ($contractors as $contractor) {
                    $token = $this->uniqueToken();

                    DB::table('contractors')
                        ->where('id', $contractor->id)
                        ->update(['statement_token' => $token]);
                }
            });

        Schema::table('contractors', function (Blueprint $table) {
            $table->unique('statement_token');
        });
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropUnique(['statement_token']);
            $table->dropColumn('statement_token');
        });
    }

    private function uniqueToken(): string
    {
        do {
            $token = Str::random(48);
        } while (DB::table('contractors')->where('statement_token', $token)->exists());

        return $token;
    }
};
